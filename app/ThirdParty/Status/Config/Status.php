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
      0  => '택배비 산정 중',
    100  => '산정완료',
  ];

  public $paymentStatus = [
      -1  => '오류 또는 처리중',
    -100  => '결제 취소',
    -200  => '환불',
       0  => '결제 전',
     100  => '결제 완료',
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
    // if ( empty($type) ) return;
    
    $result;
    switch($type) {
      case 'product':
        $result['export'] = [ [ 'header'  => '브랜드명',
                                'headerValid' => '브랜드명',
                                'field'   => 'brand'  ],
                              [ 'header'  => 'barcode',
                                'headerValid' => 'barcode',
                                'field'   => 'barcode',
                                'opts'    => [  'width'   => 20,
                                                'dataType'  =>  'string' ] ],
                              [ 'header'  => 'Product Code',
                                'headerValid' => 'Product Code',
                                'field'   => 'productCode',
                                'opts'    => [  'width'   => 20,
                                                'dataType'  => 'string'  ] ],
                              [ 'header'  => '=concat("상품Thumbnail",char(10),"파일경로")',
                                'headerValid' => '상품Thumbnail 파일경로',
                                'field'   => 'img_url',
                                'opts'    => [  'width'   => 30   ] ],
                              [ 'header'  => 'Product Name (KOR)',
                                'headerValid' => 'Product Name (KOR)',
                                'field'   => 'name',
                                'opts'    => [  'width'   => 50] ],
                              [ 'header'  => 'Product Name (ENG)',
                                'headerValid' => 'Product Name (ENG)',
                                'field'   => 'name_en',
                                'opts'    => [  'width'   => 50] ],
                              [ 'header'  => 'Box(묶음) 구성',
                                'headerValid'  => 'Box(묶음) 구성',
                                'field'   => 'box' ],       
                              [ 'header'  => '=concat("박스 내 상품",char(10),"형태",char(10),"1:다른상품 조합")',
                                'headerValid'  => '박스 내 상품 형태 1:다른상품 조합',
                                'field'   => 'contents_type_of_box',
                                'opts'    => [  'width' =>  16  ]],
                              [ 'header'  => '박스 내 상품개수',
                                'headerValid'  => '박스 내 상품개수',
                                'field'   => 'in_the_box' ],
                              [ 'header'  => '=concat("박스내 구성품",char(10),"다른 상품 묶음")',
                                'headerValid'  => '박스내 구성품 다른 상품 묶음',
                                'field'   => 'contents_of_box' ],
                              [ 'header'  => '=concat("박스 내 상품",char(10),"스펙 상세", char(10), ",으로 구분")',
                                'headerValid'  => '박스 내 상품 스펙 상세 ,으로 구분',
                                'field'   => 'package_detail',
                                'opts'    => [  'width' =>  16  ]],
                              [ 'header'  => 'Spec',
                                'headerValid'  => 'Spec',
                                'field'   => 'spec' ],
                              [ 'header'  => 'Spec2',
                                'headerValid'  => 'Spec2', 
                                'field'   => 'spec2' ],
                              [ 'header'  => '=concat("용기상품",char(10),"1:용기상품",char(10),"예)아이패치")',
                                'headerValid'  => '용기상품 1:용기상품 예)아이패치',
                                'field'   => 'container',
                                'opts'    => [  'width' =>  16  ] ],
                              [ 'header'  => '=concat("상품스펙 상세",char(10),"예) 1.5g")',
                                'headerValid'  => '상품스펙 상세 예) 1.5g',
                                'field'   => 'spec_detail',
                                'opts'    => [  'dataType'  => 'string' ] ],
                              [ 'header'  => '=concat("Spec Piece", char(10), "예)60EA")',
                                'headerValid'  => 'Spec Piece 예)60EA',
                                'field'   => 'spec_pcs'],
                              [ 'header'  => 'Weight(g)',
                                'headerValid'  => 'Weight(g)',
                                'field'   => 'shipping_weight'],
                              [ 'header'  => 'Sample',
                                'headerValid'  => 'Sample',
                                'field'   => 'sample' ],
                              [ 'header'  => 'Type',
                                'headerValid'  => 'Type',
                                'field'    => 'type' ],
                              [ 'header'  => 'Type (ENG)',
                                'headerValid'  => 'Type (ENG)',
                                'field'   => 'type_en' ],
                              [ 'header'  => 'Set',
                                'headerValid'  => 'Set',
                                'field'   => 'package'],
                              [ 'header'  => 'Renewal',
                                'headerValid' => 'Renewal',
                                'field'   => 'renewal'],
                              [ 'header'  => '기타',
                                'headerValid' => '기타',
                                'field'   => 'etc' ],
                              [ 'header'  => '=concat("상품판매여부",char(10),"1:판매안함", char(10), "및 단종")',
                                'headerValid' => '상품판매여부 1:판매안함 및 단종',
                                'field'   => 'discontinued'],
                              [ 'header'  => '=concat("상품표시",char(10), "1:표시함")',
                                'headerValid' => '상품표시 1:표시함',
                                'field'   => 'display'] ];
        break;
      case 'supplyPrice' : 
        $result['export'] = [ 
                              // [ 'header'  => 'product_id', 
                              //   'field'   => 'product_id', 
                              //   'opts'    => ['width' => 0]  ],
                              // [ 'header'  => 'product_price_id', 
                              //   'field'   => 'product_price_id',
                              //   'opts'    => ['width' => 0]],
                              [ 'header'  => '소비자가',
                                'headerValid' => '소비자가',
                                'field'   => 'retail_price'],
                              // [ 'header'  => '=concat("공급가",char(10),"수동공급가,구분해서 입력",char(10),"예) 1000/1200")', 
                              //   'field'   => 'supply_price'],
                              [ 'header'  => '=concat("공급가",char(10),"수동공급가", char(10),"입력하기")', 
                                'headerValid' => '공급가 수동공급가 입력하기',
                                'field'   => 'supply_price'],
                              [ 'header'  => '=concat("공급률",char(10),"사용여부", char(10), "1:공급률사용")', 
                                'headerValid' => '공급률 사용여부 1:공급률사용',
                                'field'   => 'supply_rate_applied' ],
                              [ 'header'  => '공급률', 
                                'headerValid' => '공급률',
                                'field'   => 'supply_rate'],
                              [ 'header'  => '=concat("공급가 수동변경",char(10),"1:수동변경")',
                                'headerValid' => '공급가 수동변경 1:수동변경', 
                                'field'   => 'not_calculating_margin'],
                              [ 'header'  => '=concat("직접입력 값",char(10),"/로 구분",char(10),"예)A가/B가")',
                                'headerValid' => '직접입력 값 /로 구분 예)A가/B가',
                                'field'   => 'price',
                                'opts'    => [ 'dataType'  => 'string' ] ],
                              [ 'header'  => '=concat("영세",char(10),"0:영세&과세",char(10),"1:영세 2:과세")', 
                                'headerValid' => '영세 0:영세&과세 1:영세 2:과세',
                                'field'   => 'taxation'] ];        
        break;
      case 'productStock' : 
        $result['export'] = [ [ 'header'  => '사용여부', 
                                'headerValid' => '사용여부',
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
                                'headerValid' => "MOQ",
                                'field'   => 'moq'],
                              [ 'header'  => '인박스 수량',
                                'headerValid' => '인박스 수량', 
                                'field'   => 'spq_inBox'],
                              [ 'header'  => '아웃박스 수량', 
                                'headerValid' => '아웃박스 수량',
                                'field'   => 'spq_outBox'], 
                              [ 'header'  => '=concat("수량 변경 기준", char(10), "1:인박스 기준",char(10),"2:아웃박스 기준")', 
                                'headerValid' => '수량 변경 기준 1:인박스 기준 2:아웃박스 기준',
                                'field'   => 'spq_criteria',
                                'opts'    => [  'width' =>  16  ] ],
                              [ 'header'  => '=concat("연산방식",char(10),"0:더하기",char(10),"1:배수")',
                                'headerValid' => '연산방식 0:더하기 1:배수', 
                                'field'   => 'calc_code',
                                'opts'    => [  'width' =>  16 ]  ],
                              [ 'header'  => '=concat("연산할 단위", char(10), "더할때만 유효함")',
                                'headerValid' => '연산할 단위 더할때만 유효함', 
                                'field'   => 'calc_unit',
                                'opts'    => [  'width' =>  16 ]  ]  ];
        break;
      case 'default':
        $result['export'] = [ [ 'header' => 'ID', 
                                'headerValid' => 'ID',
                                'field' => 'id', 
                                'opts' => ['width' => 0]  ],
                              [ 'header' => 'Brand ID',
                                'headerValid' => 'Brand ID', 
                                'field' => 'brand_id', 
                                'opts' => ['width' => 0]  ],
                              [ 'header'  => '브랜드명', 
                                'headerValid' => '브랜드명',
                                'field'   => 'brand' ],
                              [ 'header'  => 'barcode',
                                'headerValid' => 'barcode',
                                'field'   => 'barcode',
                                'opts'    => [  'width'   => 20,
                                                'dataType'  =>  'string' ] ],
                              [ 'header'  => 'Product Name (KOR)',
                                'headerValid'  => 'Product Name (KOR)',
                                'field'   => 'name',
                                'opts'    => [  'width'   => 50] ],
                              [ 'header'  => 'Product Name (ENG)',
                                'headerValid' => 'Product Name (ENG)',
                                'field'   => 'name_en',
                                'opts'    => [  'width'   => 50] ]  ];
        break;
      case 'idOnly': 
        $result['export'] = [ [ 'header' => 'ID', 
                                'headerValid' => 'ID',
                                'field' => 'id']];
        break;
      default:
        $result['export'] = [ [ 'header' => 'ID', 
                                'headerValid' => 'ID',
                                'field' => 'id', 
                                'opts' => ['width' => 0]  ],
                              [ 'header' => 'Brand ID',
                                'headerValid' => 'Brand ID', 
                                'field' => 'brand_id', 
                                'opts' => ['width' => 0]  ] ];
        // return;
        break;
    }
    return $result;
  }
}