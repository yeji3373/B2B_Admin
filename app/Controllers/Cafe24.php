<?php
namespace App\Controllers;

class Cafe24 extends BaseController {
  protected $mallID;
  protected $clientID; // app key
  protected $receiveUrl;


  public function __construct() {
    $this->mallID = 'beautynetkr';
    $this->clientID = '45icFc2YGBpryVwZjZBkdC';
    $this->receiveUrl = 'https://beautynetkorea.com/ouath/authentication.html';
  }
}

// https://developer.cafe24.com/docs/guide/basic_app_sample_code_guide_-_admin.html
// https://developers.cafe24.com/docs/api/front/#cafe24-api
// https://developers.cafe24.com/docs/api/admin/#api-index