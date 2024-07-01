<?php

use Config\Services;
use App\Models\BrandModel;

function get_brand() {
  $brandModel = new BrandModel();
  $brands = $brandModel
              ->brands()
              ->where('brand.available', 1)
              ->orderBy('brand.brand_name ASC')
              ->find();
  return $brands;
}

function brand_select_default_options(Array $options = []) {
  $default_option = '';
  
  if ( !empty($options) ) {
    foreach($options AS $opt) {
      $default_option .= "<option value='{$opt['value']}'>{$opt['text']}</option>";
    }
  }  
  return $default_option;
}

function brand_options(Array $opt = []) {
  $options = '';
  $request = Services::request();
  $brands = get_brand();

  // var_dump($opt[0]['data-opt']);

  if ( !empty($brands) ) {
    $selected = '';
    
    foreach ( $brands AS $brand ) {
      $data_opt = '';
      if ( $request->getGet('brand_id') == $brand['brand_id'] ) {
        $selected = "selected";
      }

      if( !empty($opt) ) {
        foreach( $opt AS $op ) {
          if ( !empty($op['data-opt']) )  {
            foreach( $op['data-opt'] as $data ) {
              $data_opt .= $data['name']." = '".$brand[$data['value']].(isset($data['opt']) ? $data['opt'] : '')."'";
            }
          }
        }
      }

      $options .= "<option class='text-uppercase' value='{$brand['brand_id']}' 
                  $data_opt
                  $selected >".
                  stripslashes(htmlspecialchars($brand['brand_name']))."</option>";
    }
  }
  return $options;
}

if ( ! function_exists('brand_select') ) {
  function brand_select(Array $selectOpt = [], Array $defaultOpt = [], Array $valueOpt = []) {
    $selectName = 'brand_id';
    $selectClass = '';

    if ( !empty($selectOpt) ) {
      if ( isset($selectOpt['name']) ) $selectName = $selectOpt['name'];
      if ( isset($selectOpt['class']) ) $selectClass = $selectOpt['class'];
    }

    $select_html = "<select class='form-select form-select-sm text-uppercase $selectClass' name='$selectName'>".
                      brand_select_default_options($defaultOpt).
                      brand_options($valueOpt).
                    "</select>";

    return $select_html;
  }
}