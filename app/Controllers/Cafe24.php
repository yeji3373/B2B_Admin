<?php
namespace App\Controllers;

use App\Models\Cafe24Model;

class Cafe24 extends BaseController {
  protected $mallID;
  protected $clientID; // app key
  protected $receiveUrl;


  public function __construct() {
    $this->cafe24Ip = new Cafe24Model();

    $this->mallID = 'beautynetkr';
    $this->clientID = '45icFc2YGBpryVwZjZBkdC';
    $this->receiveUrl = 'https://beautynetkorea.com/ouath/authentication.html';

    $this->data['header'] = ['css' => ['/table.css'], 'js' => ['/cafe24.js']];
  }

  public function ipList() {

    $this->data['nation'] = $this->cafe24Ip->getCountries();

    $params = $this->request->getGet();

    if(!empty($params)){
      if(!empty($params['ip_nation'])){
        $this->cafe24Ip->where('ip_nation', $params['ip_nation']);
      }
      if(!empty($params['ip'])){
        $this->cafe24Ip->like('ip', $params['ip']);
      }
      if(!empty($params['corp_name'])){
        $this->cafe24Ip->like('corp_name', $params['corp_name']);
      }
    }

    $this->data['list'] = $this->cafe24Ip->getCafe24IpByPages()->paginate(20);
    $this->data['pager'] = $this->cafe24Ip->pager;
    return $this->menuLayout('cafe24/ipList', $this->data);
  }

  public function ipRegister() {
    
    return redirect()->back();
  }
}

// https://developer.cafe24.com/docs/guide/basic_app_sample_code_guide_-_admin.html
// https://developers.cafe24.com/docs/api/front/#cafe24-api
// https://developers.cafe24.com/docs/api/admin/#api-index