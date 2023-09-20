<?php
namespace Status\Config;

use CodeIgniter\Config\BaseConfig;

define('IMAGEURL', '//beautynetkorea.daouimg.com');

class Status extends BaseConfig {
  // public function imageSrc($url = '', Array $src = []) {
  public function imageSrc($url = NULL, $src = NULL) {
    $imageSrc = IMAGEURL."/b2b/documents/common/no-image.png";

    if ( strpos($src, 'no-image') === false ) {
      $imageSrc = $src;
    }
    // if ( strpos($src, 'no-image.png') === true ) { $url = NULL; }
    // switch($url) {
    //   case 'brand' :
    //     $imageSrc = IMAGEURL."/b2b/{$src['brand']}/{$src['name']}";
    //     break;
    //   case 'prd' :
    //     $imageSrc = $src;
    //     break;
    //   default :
    //     $imageSrc = IMAGEURL."/b2b/documents/common/no-image.png";
    //     break;
    // }
    return $imageSrc;
  }

  public $deliveryCode = [
    0 => '택배비 산정 중',
    100 => '산정완료',
  ];

  public $paymentStatus = [
    0 => '결제 전',
    100 => '결제 완료',
    -100 => '결제 취소',
    -200 => '환불'
  ];

  public function paymentStatus($i = null) {
    $status = '';
      
    switch($i) {
      case 0 :
        $status = '결제 전';
        break;
      case 100 :
        $status = '결제 완료';
        break;
      case -100: 
        $status = '결제 취소';
        break;
      case -200 :
        $status ='환불';
        break;
      default:
        $status = 'error';
        break;
    }

    return $status;
  }

  public function getHeader($type = null) {
    if ( empty($type) ) return;
    
    $result;
    switch($type) {
      case 'product':
        $result['export'] = [ [ 'header'  => '브랜드명', 
                                'field'   => 'brand' ],
                              [ 'header'  => 'barcode',
                                'field'   => 'barcode',
                                'opts'    => [  'width'   => 20,
                                                'dataType'  =>  'string' ] ],
                              [ 'header'  => 'Product Code',
                                'field'   => 'productCode',
                                'opts'    => [  'width'   => 20,
                                                'dataType'  => 'string'  ] ],
                              [ 'header'  => '=concat("상품Thumbnail",char(10),"파일경로")',
                                'field'   => 'img_url',
                                'opts'    => [  'width'   => 30   ] ],
                              [ 'header'  => 'Product Name (KOR)',
                                'field'   => 'name',
                                'opts'    => [  'width'   => 50] ],
                              [ 'header'  => 'Product Name (ENG)',
                                'field'   => 'name_en',
                                'opts'    => [  'width'   => 50] ],
                              [ 'header'  => 'Box(묶음) 구성',
                                'field'   => 'box' ],       
                              [ 'header'  => '=concat("박스 내 상품",char(10),"형태",char(10),"1:다른상품 조합")',
                                'field'   => 'contents_type_of_box' ],                         
                              [ 'header'  => '박스 내 상품개수',
                                'field'   => 'in_the_box' ],
                              [ 'header'  => '=concat("박스내 구성품",char(10),"다른 상품 묶음")',
                                'field'   => 'contents_of_box' ],
                              [ 'header'  => '=concat("박스 내 상품",char(10),"스펙 상세", char(10), ",으로 구분")',
                                'field'   => 'package_detail'],                                
                              [ 'header'  => 'Spec',
                                'field'   => 'spec' ],
                              [ 'header'  => 'Spec2', 
                                'field'   => 'spec2' ],
                              [ 'header'  => '=concat("container",char(10),"1:용기상품",char(10),"예)아이패치")',
                                'field'   => 'container' ],
                              [ 'header'  => '=concat("상품스펙 상세",char(10),"예) 1.5g")',
                                'field'   => 'spec_detail',
                                'opts'    => [  'dataType'  => 'string' ] ],
                              [ 'header'  => '=concat("Spec Piece", char(10), "예)60EA")',
                                'field'   => 'spec_pcs'],
                              [ 'header'  => 'Weight(g)',
                                'field'   => 'shipping_weight'],
                              [ 'header'  => 'Sample',
                                'field'   => 'sample' ],
                              [ 'header'  => 'Type',
                                'field'    => 'type' ],
                              [ 'header'  => 'Type (ENG)',
                                'field'   => 'type_en' ],
                              [ 'header'  => 'Set',
                                'field'   => 'package'],
                              [ 'header'  => 'Renewal',
                                'field'   => 'renewal'],
                              [ 'header'  => '기타',
                                'field'   => 'etc' ],
                              [ 'header'  => '=concat("상품판매여부",char(10),"1:판매안함")',
                                'field'   => 'discontinued'],
                              [ 'header'  => 'Display 1:표시함',
                                'field'   => 'display'] ];
        break;
      case 'supplyPrice' : 
        $result['export'] = [ [ 'header'  => '소비자가',
                                'field'   => 'retail_price'],
                              // [ 'header'  => '=concat("공급가",char(10),"수동공급가,구분해서 입력",char(10),"예) 1000/1200")', 
                              //   'field'   => 'supply_price'],
                              [ 'header'  => '=concat("공급가",char(10),"수동공급가 입력")', 
                                'field'   => 'supply_price'],
                              [ 'header'  => '=concat("공급률 사용여부",char(10),"1:공급률사용")', 
                                'field'   => 'supply_rate_applied' ],
                              [ 'header'  => '공급률', 
                                'field'   => 'supply_rate'],
                              [ 'header'  => '=concat("공급가 수동변경",char(10),"1:수동변경")', 
                                'field'   => 'not_calculating_margin'],
                              [ 'header'  => '=concat("직접입력 값",char(10),"/로 구분해서 입력",char(10),"예) 1000(A)/1200(B)")',
                                'field'   => 'price',
                                'opts'    => [ 'dataType'  => 'string' ] ],
                              [ 'header'  => '=concat("영세",char(10),"0:영세&과세",char(10),"1:영세 2:과세")', 
                                'field'   => 'taxation'] ];        
        break;
      case 'productStock' : 
        $result['export'] = [ [ 'header'  => '사용여부', 
                                'field'   => 'available'],
                              [ 'header'  => '입고수량', 
                                'field'   => 'supplied_qty'],
                              [ 'header'  => '제품위치', 
                                'field'   => 'layout_section'],
                              [ 'header'  => '유효기간', 
                                'field'   => 'exp_date'] ];
        $result['fields'] = ['available', 'supplied_qty', 'layout_section', 'exp_date'];
        break;
      case 'productSpq' :  // moq
        $result['export'] = [ [ 'header'  => 'MOQ', 
                                'field'   => 'moq'],
                              [ 'header'  => '인박스 수량', 
                                'field'   => 'spq_inBox'],
                              [ 'header'  => '아웃박스 수량', 
                                'field'   => 'spq_outBox'], 
                              [ 'header'  => '=concat("수량 변경 기준", char(10), 1:인박스 수량 기준",char(10),"2:아웃박스 수량 기준")', 
                                'field'   => 'spq_criteria'],
                              [ 'header'  => '=concat("연산방식",char(10),"0:더하기",char(10),"1:배수")', 
                                'field'   => 'calc_code'],
                              [ 'header'  => '=concat("연산할 단위", char(10), "숫자입력", char(10), "연산방식에 주의")', 
                                'field'   => 'calc_unit'] ];
        break;
      default: 
        return;
        // break;
    }
    return $result;
  }
}