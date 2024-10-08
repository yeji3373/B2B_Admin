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
use VerifyEmail\Controllers\VerifyemailController;

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

    $params = $this->request->getVar();

    $dateYn = 0;

    if( !empty($params) ){
      if( !empty($params['buyer_name']) ){
        $this->buyers->like('buyers.name', $params['buyer_name']);
      }
      
      if( isset($params['dateYn']) || !empty($params['dateYn'])){
        $dateYn = 1;
      }

      if( empty($dateYn) ) {
        if(!empty($params['start_date']) && !empty($params['end_date'])){
          $this->buyers->where('DATE(buyers.created_at) >=', $params['start_date']);
          $this->buyers->where('DATE(buyers.created_at) <=', $params['end_date']);
        }
      }
      if( !empty($params['managers']) ) {
        $this->buyers->where('buyers.manager_id', $params['managers']);
        if($params['managers'] == -1){
          $this->buyers->where('buyers.manager_id IS NULL', NULL, true);
        }
      }
      if( !empty($params['regions']) ) {
        $this->buyers->where("buyers.region_ids IN ({$params['regions']})");
      }
      if( !empty($params['margin']) ) {
        $this->buyers->where('buyers.margin_level', $params['margin']);
      }
      if( isset($params['confirmation']) ) {
        if( $params['confirmation'] == 0 ){
          $this->buyers->where('buyers.confirmation', $params['confirmation']);
        }else if($params['confirmation'] == 1){
          $this->buyers->where('buyers.confirmation', $params['confirmation']);
        }
      }
      // print_r($params);
    }

    
    $first = $this->getManagers()->where(['role_id' => 2])->findAll();
    $add[] = array('idx' => -1, 'name' => '미지정');
    $managers = array_merge($first, $add);
   
    $this->data['dateYn'] = $dateYn;
    $this->data['managers'] = $managers;
    $this->data['regions'] = $this->getRegion()->findAll();
    $this->data['margin'] = $this->margin->where('available', 1)->findAll();
    $this->data['buyers'] = $this->getBuyersByPages();
    
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
        $countries_ids = explode(",", $this->data['buyer']['countries_ids']);
        $this->data['countries'] = $this->getCountry()->whereIn("id", $countries_ids)->findAll();
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

    $verifyEmail =  new VerifyemailController();
    if ( !empty($req['user']) && !empty($req['user']['email']) ) {
      if ( !$verifyEmail->check($req['user']['email']) ) {
        $this->buyers->save(['id' => $req['buyer']['id'], 'available' => -1]);
        return redirect()->back()->with('error', 'email 주소가 유효하지 않아서 계정 정보가 삭제됩니다.');
      }
    } else {
      $this->buyers->save(['id' => $req['buyer']['id'], 'available' => -1]);
      return redirect()->to(base_url('buyer/list'))->with('error', '정확한 정보가 없는 경우입니다. 삭제처리 됩니다');
    }
    var_dump($req);
    return;

    if ( session()->has('redirect') ) $redirect = session()->getFlashdata('redirect');
    
    print_r($req);
    if ( empty($req) ) return redirect()->back()->withInput()->with('error', 'input 정보가 없음');

    if ( !empty($req['buyer']) ) {
      $buyer = $req['buyer'];
      if ( $buyer['deposit_rate'] > 100 ) $buyer['deposit_rate'] = 100;
      $buyer['deposit_rate'] = ( $buyer['deposit_rate'] / 100 );
      

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
                  ->where(['buyers.available !=' => -1]);
    return $buyers;
  }

  public function getBuyersByPages() {
    $page = null;
    $buyers = $this->buyers
                  ->select('buyers.*, manager.name AS manager_name')
                  ->join('users', 'users.buyer_id = buyers.id')
                  ->join('manager', 'manager.idx = buyers.manager_id', 'left outer')
                  ->where(['buyers.available !=' => -1])
                  ->paginate(30);
    $this->data['pager'] = $this->buyers->pager;
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
