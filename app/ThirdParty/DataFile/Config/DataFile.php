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
  public $cols = 0;

  public function setCoordinate($headerCnt = 0) {
    if ( !empty($headerCnt) ) {
      for ( $i = 0,$CHR = 'A';  $i < $headerCnt; $i++,$CHR++ ) {
        $this->coordinate[$i] = $CHR;
      }
    }
  }

  public function setHeader($header = array(), $col = 1) {
    if ( empty($header) ) {
    } else {
      $this->cols = $col;
      $this->setCoordinate(count($header));
      foreach($header AS $i => $head) {
        array_push($this->headers, [$this->coordinate[$i].$this->cols, $head]);
        // echo $this->coordinate[$i].$row." ".$head."<br/>";
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