<?php

use Config\Services;

if ( ! function_exists('brand_select') ) {
  function brand_select(string $field) {
    $request = Services::request();
    $input = $request->getOldInput($field);

    if ( $input == null ) {
      $input = $request->getPost($field);
    }
  }
}