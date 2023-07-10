<?php
namespace Status\Config;

use CodeIgniter\Config\BaseConfig;

define('IMAGEURL', 'https://beautynetkorea.daouimg.com');

class Status extends BaseConfig {
  public function imageSrc($url = '', Array $src = []) {
    $imageSrc;
    
    switch($url) {
      case 'brand' :
        $imageSrc = IMAGEURL."/b2b/{$src['brand']}/{$src['name']}";
        break;
      case 'prd' :
        $imageSrc = $src;
      default :
        $imageSrc = IMAGEURL."/b2b/documents/common/no-image.png";
        break;
    }

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
        // $result['header'] = [ 'Barcode', 'Product Code', '=concat("상품Thumbnail",char(10),"파일경로")', '브랜드명', 
        //                       'Product Name (KOR)', 'Product Name (ENG)', 'Box(묶음) 구성', '박스 내 상품개수', 
        //                       '=concat("박스내 구성품",char(10),"다른 상품 묶음")', 'Spec', 'Spec2', '=concat("container",char(10),"1:용기상품",char(10),"예)아이패치")',
        //                       '=concat("상품스펙 상세",char(10),"예) 1.5g")', '=concat("Spec Piece", char(10), "예) 60EA")', 'Weight(g)', 'Sample', 
        //                       'Type', 'Type (ENG)', 'Set', 'Set Detail', 
        //                       'Renewal', '기타', '=concat("상품판매여부",char(10),"1:판매안함")', 'Display 1:표시함'];
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
                                'type'    => 'type' ],
                              [ 'header'  => 'Type (ENG)',
                                'field'   => 'type_en' ],
                              // [ 'header'  => 'Set',
                              //   'field'   => 'package'],
                              [ 'header'  => 'Renewal',
                                'field'   => 'renewal'],
                              [ 'header'  => '기타',
                                'field'   => 'etc' ],
                              [ 'header'  => '=concat("상품판매여부",char(10),"1:판매안함")',
                                'field'   => 'discontinued'],
                              [ 'header'  => 'Display 1:표시함',
                                'field'   => 'display'] ];
        // fields에 brand_id, id는 각각 가공하는 곳에서 필요시에 따라 추가        
        $result['fields'] = [ 'brand_name', 'barcode', 'productCode', 'img_url',
                              'name', 'name_en', 'box', 'in_the_box', 
                              'contents_of_box', 'spec', 'spec2', 'container',
                              'spec_detail', 'spec_pcs', 'shipping_weight', 'sample', 
                              'type', 'type_en', 'package', 'package_detail', 
                              'renewal', 'etc', 'discontinued', 'display'];
        // $result['option'] = [['B', 17]]; // 아직 안쓰고 있음
        break;
      case 'supplyPrice' : 
        // $result['header'] = [ '소비자가', '=concat("공급가",char(10),"수동공급가,구분해서 입력",char(10),"예) 1000/1200")', 
        //                       '=concat("공급률 사용여부",char(10),"1:공급률사용")', 
        //                       '공급률', '=concat("공급가 수동변경",char(10),"1:수동변경")', 
        //                       '=concat("직접입력 값",char(10),"/로 구분해서 입력")', 
        //                       '=concat("영세",char(10),"0:영세&과세",char(10),"1:영세 2:과세")'];
        $result['export'] = [ [ 'header'  => '소비자가', ],
                              [ 'header'  => '=concat("공급가",char(10),"수동공급가,구분해서 입력",char(10),"예) 1000/1200")', ],
                              [ 'header'  => '=concat("공급률 사용여부",char(10),"1:공급률사용")', ],
                              [ 'header'  => '공급률', ],
                              [ 'header'  => '=concat("공급가 수동변경",char(10),"1:수동변경")', ],
                              [ 'header'  => '=concat("직접입력 값",char(10),"/로 구분해서 입력",char(10),"예) 1000(A)/1200(B)")',
                                'opts'    => [ 'dataType'  => 'string' ] ],
                              [ 'header'  => '=concat("영세",char(10),"0:영세&과세",char(10),"1:영세 2:과세")', ] ];
        // $result['fields'] = ['product_idx', 'retail_price', 'supply_price', 'supply_rate_applied', 
        //                       'supply_rate', 'not_calculating_margin', 'price', 'taxation'];
        $result['fields'] = [ 'retail_price', 'supply_price', 'supply_rate_applied', 
                              'supply_rate', 'not_calculating_margin', 'price', 'taxation'];
        break;
      case 'productStock' : 
        // $result['header'] = ['사용여부', '입고수량', '제품위치', '유효기간'];
        $result['export'] = [ [ 'header'  => '사용여부', ],
                              [ 'header'  => '입고수량', ],
                              [ 'header'  => '제품위치', ],
                              [ 'header'  => '유효기간', ] ];
        $result['fields'] = ['available', 'supplied_qty', 'layout_section', 'exp_data'];
        break;
      case 'productSpq' :  // moq
        $result['header'] = [ 'MOQ', '인박스 수량', '아웃박스 수량', '=concat("0:인박스 수량 기준",char(10),"1:아웃박스 수량 기준")',
                              '=concat("연산방식",char(10),"0:더하기",char(10),"1:배수")',
                              '연산할 단위'];
        $result['export'] = [ [ 'header'  => 'MOQ', ],
                              [ 'header'  => '인박스 수량', ],
                              [ 'header'  => '아웃박스 수량', ], 
                              [ 'header'  => '=concat("0:인박스 수량 기준",char(10),"1:아웃박스 수량 기준")', ],
                              [ 'header'  => '=concat("연산방식",char(10),"0:더하기",char(10),"1:배수")', ],
                              [ 'header'  => '연산할 단위', ] ];
        $result['fields'] = ['moq', 'spq_inBox', 'spq_outBox', 'spq',
                              'calc_code', 'calc_unit'];
        break;
      default: 
        return;
        // break;
    }
    return $result;
  }
}