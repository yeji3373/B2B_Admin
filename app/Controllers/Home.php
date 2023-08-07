<?php

namespace App\Controllers;

use App\Controllers\Buyer;
use App\Models\CurrencyModel;
use App\Models\CurrencyRateModel;

use App\Controllers\Orders;

class Home extends BaseController
{
  public function __construct() {
    $this->buyers = new Buyer();
    $this->currency = new CurrencyModel();
    $this->cRate = new CurrencyRateModel();

    $this->orderController = new Orders;

    $this->limit = 15;
  }

  public function index() {
    $data['header'] = ['css' => ['/table.css', '/main.css'],
                       'js'  => ['/main.js', '/example.js']];

    $data['buyers'] = $this->buyers->getBuyers()->where(['confirmation' => 0])->findAll($this->limit);
    $data['managers'] = $this->buyers->getManagers()->where(['role_id' => 2])->findAll($this->limit);
    $data['currencies'] = $this->currency
                              ->currency()
                              ->where(['currency.available' => 1
                                    , 'currency_rate.available' => 1
                                    , 'currency_rate.default_set' => 1])
                              ->findAll();
    $data['default_currency'] = $this->currency->currency()->where(['currency.default_currency'=> 1, 'currency.available'=> 1, 'currency_rate.default_set' => 1, 'currency_rate.available' => 1])->first();
    $data['orders'] = $this->orderController->getOrders()->orderBy('orders.id DESC')->findAll($this->limit);
        
    return $this->menuLayout('dashboard/main', $data);
  }

  public function currency() {
    $data = $this->request->getPost();

    $cRateCheck = $this->cRate->where(['cRate_idx' => $data['cRate_idx']
                                      , 'default_set' => 1])
                              ->first();

    if ( !empty($cRateCheck) ) {
      if ( $cRateCheck['currency_idx'] == $data['currency_idx'] ) {
        if ( $cRateCheck['exchange_rate'] != $data['exchange_rate']) {
          if ( $this->cRate->save(['cRate_idx' => $data['cRate_idx'], 'available' => 0]) ) {
            $this->cRate->save(['exchange_rate'=> $data['exchange_rate']
                              , 'currency_idx' => $data['currency_idx']
                              , 'default_set' => 1
                              , 'available' => 1]);
          }
        } else {
          return redirect()->back()->with('error', '동일한 환율 적용됨');
        } // 동일한 값은 그냥 return
      } else redirect()->back()->with('error', 'currency 잘못 선택'); // currency 잘못 선택
    }

    return redirect()->back()->with('error', '환율설정 완료');
  }

  public function test() {
    $data['header'] = ['css' => ['/table.css', '/main.css'],
                        'js'  => ['/main.js', '/example.js']];
    
    return $this->menuLayout('dashboard/example', $data);
  }
}
