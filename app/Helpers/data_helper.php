<?php

use Config\Services;

function encording_check($str) {
  $iconv_convert = null;
  
  if ( mb_detect_encoding($str) != 'UTF-8' )  $iconv_convert = iconv('euc-kr', 'utf-8', $str);
  else $iconv_convert = $str;
  
  return $iconv_convert;
}