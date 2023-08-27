<?php

namespace App\Controllers;

use App\Models\BuyersModel;
use App\Models\RegionModel;
use App\Models\CountryModel;
use App\Models\CurrencyModel;
use App\Models\CurrencyRateModel;
use App\Models\MarginModel;
use App\Models\BuyersCurrencyModel;
use App\Models\UsersModel;
use Auth\Models\UserModel;

class Buyer extends BaseController
{
  public function __construct() {
    $this->buyers = new BuyersModel();
    $this->margin = new MarginModel();
    $this->currency = new CurrencyModel();
    $this->currencyRate = new CurrencyRateModel();
    $this->buyersCurrency = new BuyersCurrencyModel();
    $this->users = new UsersModel();

    $this->data['header'] = ['css' => ['/buyers/buyer.css', '/table.css']
                            ,'js' => ['/buyers/buyers.js'] ];
  }

  public function index() {
    // return $this->menuLayout('dashboard/main', $data);
  }

  public function list() {
    $this->data['buyers'] = $this->getBuyers()->findAll();
    return $this->menuLayout('buyer/list', $this->data);
  }
  
  public function info() {
    $id = $this->request->uri->getSegment(3);

    session()->setFlashdata('redirect', site_url(previous_url()));

    $this->data['regions'] = [];
    $this->data['countries'] = [];
    $this->data['currency'] = [];
    $this->data['margin'] = [];
    
    $this->data['buyer'] = $this->getBuyers()->where(['buyers.id'=> $id])->first();
    $this->data['managers'] = $this->getManagers()->where(['role_id' => 2])->findAll();

    $this->data['users'] = $this->users->where('buyer_id', $id)->findAll();

    if ( !empty($this->data['buyer']) ) {
      if ( !empty($this->data['buyer']['region_ids']) ) {
        $this->data['regions'] = $this->getRegion("id IN ( {$this->data['buyer']['region_ids']} )")->findAll();
      }
      if ( !empty($this->data['buyer']['countries_ids']) ) {
        $this->data['countries'] = $this->getCountry()->whereIn("id", $this->data['buyer']['countries_ids'])->findAll();
      }
      $this->data['margin'] = $this->margin->where('available', 1)->findAll();
      $this->data['currency'] = $this->currency->currency()
                                  ->select("currency.idx, currency.currency_code, currency.default_currency")
                                  ->select('currency_rate.cRate_idx, currency_rate.exchange_rate, currency_rate.available')
                                  ->where(['currency_rate.available'=> 1, 'currency.currency_code != ' => 'KRW'])
                                  ->findAll();
    }

    return $this->menuLayout('buyer/detail', $this->data);
  }

  public function edit() {
    $req = $this->request->getPost();
    $buyer = []; $currencyRate = []; $buyerCurrency = []; $user = [];
    $redirect = null;

    if ( session()->has('redirect') ) $redirect = session()->getFlashdata('redirect');
    
    print_r($req);
    if ( empty($req) ) return redirect()->back()->withInput()->with('error', 'input 정보가 없음');

    if ( !empty($req['buyer']) ) {
      $buyer = $req['buyer'];
      if ( $buyer['deposit_rate'] > 100 ) $buyer['deposit_rate'] = 100;
      $buyer['deposit_rate'] = ( $buyer['deposit_rate'] / 100);
      

      if ( $buyer['confirmation'] == 1 ) {
        $buyer['available'] = 1;
      }
    }

    if ( !empty($req['currencyRate']) ) {
      echo "<br/><br/>currencyRate<br/>";
      if ( !empty($req['currencyRate']['currency_idx'])) { 
        $currencyRate = $req['currencyRate'];
        $buyerCurrency = [];

        $currencies = $this->currencyRate
                            ->where(['currency_idx'=> $currencyRate['currency_idx'], 'exchange_rate'=> $currencyRate['exchange_rate']])
                            ->first();
        
        if ( !empty($currencies) ) {
          if ( $currencies['available'] == 0 ) {
            $this->currencyRate->update('available', 1)->where('cRate_idx', $currencies['cRate_idx']);
          }
          $buyerCurrency['cRate_idx'] = $currencies['cRate_idx'];
        } else {
          $currencyRate['available'] = 1;
          if ( $this->currencyRate->insert($currencyRate) ) {
            $buyerCurrency['cRate_idx'] = $this->currencyRate->getInsertID();
          }
        }
        
        if ( !empty($buyerCurrency) ) {
          $buyerCurrency['buyer_id'] = $req['buyer']['id'];
          
          $buyerCurrent = $this->buyersCurrency->where(['buyer_id'=> $buyerCurrency['buyer_id'], 'cRate_idx'=> $buyerCurrency['cRate_idx']])->first();
          if ( !empty($buyerCurrent) ) {
            if ( $buyerCurrent['available'] == 0 ) {
              $buyerCurrency['id'] = $buyerCurrent['id'];
              $buyerCurrency['available'] = 1;
              if ( !$this->buyersCurrency->save($buyerCurrency) ) {
                return redirect()->back()->withInput()->with('error', '환율 우대 적용중 오류');
              }
            }
          } else {
            $buyerCurrency['available'] = 1;
            if (!$this->buyersCurrency->save($buyerCurrency)) {
              return redirect()->back()->withInput()->with('error', '환율 우대 적용중 오류');
            }
          }
        }
      }
    }

    if ( !empty($req['user']) ) {
      $user = $req['user'];

      foreach ( $user as $u ) {
        if ( !$this->users->save($u) ) {
          return redirect()->back()->withInput()->with('error', '로그인 정보 수정 중 오류');
        }
      }
    }

    if ( !$this->buyers->save($buyer) ) {
      return redirect()->back()->withInput()->with('error', '승인 처리 중 오류 발생');
    } else {
      if ( empty($redirect) ) return redirect()->to(site_url('/'));
      else return redirect()->to(site_url($redirect));
    }
  }

  public function getBuyers() {
    $buyers = $this->buyers
                  ->select('buyers.*, manager.name AS manager_name')
                  ->join('manager', 'manager.idx = buyers.manager_id', 'left outer')
                  ->where(['buyers.available > ' => -1]);
    return $buyers;
  }

  public function editManager() {
    $req = $this->request->getVar();

    if ( empty($req['type']) ) return;
    else {
      $type = $req['type'];
      unset($req['type']);
      
      if ( $type == 1 ) // edit
      { 
        if (!$this->buyers->save($req)) {
          // return //변경오류
        } else {

        }
      }
    }
  }
  
  public function getManagers() {
    $manager = new UserModel();

    $manager->where(['active'=> 1]);
    return $manager;
  }

  public function getRegion($w = null) {
    $region = new RegionModel();
    if ( !empty($w) ) $region->where($w);
    
    // $region->where('')
    return $region;
  }

  public function getCountry($w = []) {
    $countryModel = new CountryModel();
    
    if ( !empty($w) ) $countryModel->where($w);
    
    return $countryModel;
  }
}
