<?php

namespace DataFile\Config;

use CodeIgniter\Config\BaseConfig;

class DataFile extends BaseConfig {
  public $defaultOpts = [];
  public $headers = [];
  public $headerOpts = [];
  public $bodies = [];
  public $bodyOpts = [];
  public $coordinate = [];
  public $rows = 0;
  public $cols = 1;

  public function setCoordinate($headerCnt = 0) {
    if ( !empty($headerCnt) ) {
      for ( $i = 0,$CHR = 'A';  $i < $headerCnt; $i++,$CHR++ ) {
        $this->coordinate[$i] = $CHR;
      }
    }
  }

  public function setHeader($header = array()) {
    if ( empty($header) ) {
    } else {
      $this->setCoordinate(count($header));
      foreach($header AS $i => $head) {
        array_push($this->headers, [$this->coordinate[$i].$this->cols, $head]);
      }
    }
  }

  public function setBody($body = array()) {
    $this->cols += 1;
    foreach ($body AS $by) {
      $i = 0;
      foreach ($by AS $key => $val) {
        array_push($this->bodies, [$this->coordinate[$i].$this->cols, $val]);
        $i++;
      }
      $this->cols++;
    }
  }
}