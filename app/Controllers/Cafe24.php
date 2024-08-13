<?php
namespace App\Controllers;

use App\Models\Cafe24Model;
use App\Models\Cafe24IpSetModel;
use App\Models\CountryModel;

class Cafe24 extends BaseController {
  protected $mallID;
  protected $clientID; // app key
  protected $receiveUrl;


  public function __construct() {
    $this->cafe24Ip = new Cafe24Model();
    $this->ipSet = new Cafe24IpSetModel();

    $this->mallID = 'beautynetkr';
    $this->clientID = '45icFc2YGBpryVwZjZBkdC';
    $this->receiveUrl = 'https://beautynetkorea.com/ouath/authentication.html';

    $this->data['header'] = ['css' => ['/table.css'], 'js' => ['/cafe24.js']];
  }

  public function ipList() {
    $this->data['nation'] = $this->ipSet->getCountries();
    $this->data['countries'] = $this->ipSet->where(['cafe24_ip_set.idx' => NULL])->getCountries('RIGHT');

    $params = $this->request->getGet();

    if(!empty($params)){
      if(!empty($params['ip_nation'])){
        $this->cafe24Ip->where('ip_set_idx', $params['ip_nation']);
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
    $this->data['bnkIP'] = $this->cafe24Ip->where('own_ip', 1)->first();

    return $this->menuLayout('cafe24/ipList', $this->data);
  }

  public function ipRegister() {
    $params = $this->request->getPost();
    
    if(!empty($params)) {
      if ( isset($params['country_reg']) ) {
        $key = 'error';
        $msg;
        if ( empty($this->ipSet->where(['country_id' => $params['country_id']])->first()) ) {
          unset($params['country_reg']);
          $countryModel = new CountryModel();
          $country = $countryModel->where(['id' => $params['country_id']])->first();
        
          if ( !empty($country) ) {
            $params['country_code'] = $country['country_code'];
            if ( $this->ipSet->save($params) ) {
              $key = 'success';
              $msg = $country['name']." 등록 성공";
            } else {
              $msg = '등록 중 오류 발생';
            }
          } else {            
            $msg = '해당하는 국가 정보가 없음';
          }
        } else {
          $msg = '이미 있음';
        }
        return redirect()->back()->with($key, $msg);
      } else {
        $exist_ip = $this->cafe24Ip->where('ip', $params['modal_ip'])->first();

        if(!empty($exist_ip)){
          return redirect()->back()->with('error', '이미 등록된 IP 입니다.');
        }else{
          $data['ip'] = $params['modal_ip'];
          $data['ip_nation'] = $params['modal_ip_nation'];
          $data['corp_name'] = $params['modal_corp_name'];
          $this->cafe24Ip->save($data);
          return redirect()->back()->with('success', 'IP가 성공적으로 등록되었습니다.');
        }
      }
    }
  }

  public function bnkIpUpdate() {
    $params = $this->request->getPost();

    if(!empty($params)){
      $old_bnk_ip = $this->cafe24Ip->where('own_ip', 1)->find();

      $data['ip'] = $params['modal_bnk_ip'];
      $data['ip_nation'] = 'KR';
      $data['corp_name'] = 'beautynetkorea';
      $data['own_ip'] = $params['modal_bnk_yn'];

      if(empty($old_bnk_ip)){
        $this->cafe24Ip->save($data);
        return redirect()->back()->with('success', 'beautynetkorea IP가 성공적으로 등록 되었습니다.');
      }else{
        if(count($old_bnk_ip) == 1){
          $data['idx'] = $old_bnk_ip[0]['idx'];
          $this->cafe24Ip->save($data);
          return redirect()->back()->with('success', 'beautynetkorea IP가 성공적으로 업데이트 되었습니다.');
        }else{
          return redirect()->back()->with('error', 'beautynetkorea IP가 한개 이상입니다.');
        }
      }
    }
  }

  public function ipDelete() {
    $response = service('response');
    $response->setStatusCode(200);              
    $params = $this->request->getPost();
    $session = \Config\Services::session();

    $return = [];
    if(!empty($params)){
      if ( array_key_exists('idx', $params) ) {
        if ( !empty($params['idx']) ) {
          $count = 0;
          foreach($params['idx'] as $i => $idx){
            if($this->cafe24Ip->delete(['idx' => $idx])){
              $count++;
            }else{
              $response->setStatusCode(400);  
              array_push($return, ['msg' => $params['ips'][$i].' IP를 삭제하는 도중 문제가 발생했습니다.']);
              $session->setFlashdata('error', $return[0]['msg']);
              break;
            };
          }
          $session->setFlashdata('success', $count."개 IP가 성공적으로 삭제되었습니다.");
        }
      }
    }
    
    if ( $this->request->isAJAX() ) {
      $response->setJSON($return);
      return $response;
    }
    return;
  }

  public function ipSetting() {
    return $this->menuLayout('cafe24/ipSetting', $this->data);
  }
}

// https://developer.cafe24.com/docs/guide/basic_app_sample_code_guide_-_admin.html
// https://developers.cafe24.com/docs/api/front/#cafe24-api
// https://developers.cafe24.com/docs/api/admin/#api-index