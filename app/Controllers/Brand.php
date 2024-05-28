<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\BrandModel;
use App\Models\BrandOptModel;
use App\Models\ProductSpqModel;
use App\Models\StockModel;
use App\Models\ProductPriceModel;
use App\Models\SupplyPriceModel;
use App\Models\MarginModel;
use App\Models\MarginRateModel;
use App\Models\CartModel;
use Status\Config\Status;

class Brand extends BaseController {
  public function __construct() {
    helper('data');
    $this->status = config('Status');
    $this->products = new ProductModel();
    $this->brands = new BrandModel();
    $this->brandOpt = new BrandOptModel();
    $this->productSpq = new ProductSpqModel();
    $this->stocks = new StockModel();
    $this->margin = new MarginModel();
    $this->marginRate = new MarginRateModel();
    $this->productPrice = new ProductPriceModel();
    $this->supplyPrice = new SupplyPriceModel();
    $this->cart = new CartModel();

    $this->data['header'] = ['css' => ['/brand/brand.css', '/table.css']
                            ,'js' => ['/brand/brand.js']];
    $this->data['status'] = $this->status;
  }

  public function index() {
    $orderBy = 'brand.brand_id ASC, brand.own_brand DESC ';
    $this->data['brands'] = $this->brands
                              // ->select('brand.*')
                              ->select('brand.brand_id, brand.brand_name, brand.own_brand, brand.available')
                              ->select('brand_opts.idx AS brand_opt_idx
                                      , brand_opts.supply_rate_based
                                      , brand_opts.supply_rate_by_brand
                                      , brand_opts.available AS opts_available')
                              ->join('brand_opts', 'brand_opts.brand_id = brand.brand_id', 'left outer')
                              ->orderBy($orderBy)
                              ->findAll();
    $this->data['margin'] = $this->margin->findAll();

    if ( !empty($this->data['brands']) ) {
      foreach ($this->data['brands'] as $i => $brand ) {
        $this->data['brands'][$i]['marginRate'] = $this->marginRate->where(['brand_id' => $brand['brand_id']])->find();
      }
    }
    
    return $this->menuLayout('brand/main', $this->data);
  }

  public function edit() {
    if ( !empty($this->request->getVar()) ) {
      $data = $this->request->getVar();
      
      if ( empty($data['brand_id'])) return;

      $margin_rates = array();

      var_dump($data);

      // if ( !empty($data['margin_rate']) ) $margin_rates = array_values($data['margin_rate']);

      if ( !empty($data['brand'] ) ) {
        $getBrand = $this->brands->where(['brand_id' => $data['brand_id']])->first();
        if ( !empty($getBrand) ) {
          unset($getBrand['brand_registration_date']);
          
          $data['brand']['brand_id'] = $data['brand_id'];
          $diff = array_diff($getBrand, $data['brand']);
          
          if ( !empty($diff) )  {
            $this->brands->save($data['brand']);
          }
        } else return redirect()->back()->with('error', '일치하는 브랜드 정보가 없습니다.');
      }

      $getBrandOpt = array();
      if ( !empty($data['brand_opt']) )  {
        if ( !empty($data['brand_opt']['idx']) ) {
          if ( !empty($data['brand_opt']['supply_rate_by_brand']) ) {
            $data['brand_opt']['supply_rate_by_brand'] = ($data['brand_opt']['supply_rate_by_brand'] / 100);
          }
          if ( !empty($data['brand_opt']['supply_rate_based']) ) {
            $data['brand_opt']['available'] = 1;
          } else $data['brand_opt']['available'] = 0;

          $getBrandOpt = $this->brandOpt->where(['idx' => $data['brand_opt']['idx']])->first();
          if ( !empty($getBrandOpt) ) {
            unset($getBrandOpt['created_at']);
            unset($getBrandOpt['updated_at']);

            $diff = array_diff($getBrandOpt, $data['brand_opt']);
            if ( !empty($diff) ) {
              $this->brandOpt->save($data['brand_opt']);
              if ( empty($data['brand_opt']['available']) ) $getBrandOpt = [];
            } else {
              $getBrandOpt = [];
            }
          }            
        }
      }
      
      if ( !empty($data['margin_rate']) ) {
        $marginRate = $data['margin_rate'];
        foreach($marginRate AS $i => $m) {
          if ( !isset($m['available']) ) $m['available'] = 0;
          if ( !empty($m['available']) ) {
            if ( $m['margin_rate'] <= 0 ) return redirect()->back()->with('error', '마진율이 존재하지 않습니다'); 
            else $m['margin_rate'] = ($m['margin_rate'] / 100);
          }
          
          $getMarginRate = $this->marginRate->where(['idx' => $m['idx']]);
          if ( !empty($getMarginRate) ) {
            if ( !$this->marginRate->save($m) ) {
              return redirect()->back()->with('error', '마진율 수정/입력에 오류가 있습니다.');
            } else {
              $marginRate[$i]['available'] = $m['available'];
              if (isset($m['margin_rate'])) $marginRate[$i]['margin_rate'] = $m['margin_rate'];
            }
          } else return redircet()->back()->with('error', '등록된 마진율이 없습니다. 확인 부탁드립니다.');
        }
        // var_dump($marginRate);
        // return;
        $products = $this->products->where(['brand_id' => $data['brand_id']])->findAll();
        if ( !empty($products) ) {
          foreach ( $products AS $product ) {
            $prdPrice = $this->productPrice->where(['product_idx' => $product['id'], 'available'=> 1])->first();
            if ( !empty($prdPrice) ) {
              if ( empty($prdPrice['not_calculating_margin']) ) { // 마진값 직접 입력하는 값 사용 안하고, 자동으로 계산할때
                if ( !empty($prdPrice['supply_price']) ) { // 마진값 계산 시 공급가를 기준으로 하기 때문에 공급가 여부 필요
                  $prdIdx = ['product_idx' => $product['id']
                            , 'product_price_idx' => $prdPrice['idx']];

                  foreach ( $marginRate AS $m ) {
                    $price = array();
                    $price = array_merge($prdIdx, [ 'available' => $m['available'] ]);
                    if ( isset($m['margin_rate']) ) $price['price'] = ceil($prdPrice['supply_price'] * $m['margin_rate']);
                    $supplyPrice = $this
                                        ->supplyPrice
                                        ->where(array_merge($prdIdx, 
                                                            ['margin_idx'        => $m['margin_idx']
                                                            , 'margin_level'      => $m['margin_level']]))
                                        ->first();
                    if ( !empty($supplyPrice) ) {
                      $price['idx'] = $supplyPrice['idx'];
                      var_dump($m);
                      var_dump($supplyPrice);
                    } else {
                      if ( empty($m['available']) ) continue;
                      $price['margin_idx'] = $m['margin_idx']; 
                      $price['margin_level'] = $m['margin_level'];
                    }
                    var_dump($price);
                    $this->supplyPrice->save($price);
                  }
                } else return redirect()->back()->with('error', '공급가가 누락되었습니다. 공급가가 없으면 계산이 불가능합니다.');
              }
            } else {
              return redircet()->back()->with('error', '제품의 가격 정보가 없습니다.');
            }
          }
        }
      }
      // return;
      return redirect()->back();
    }
  }

  public function regist() {
    if ( !empty($this->request->getPost()) ) {
      $data = $this->request->getPost('brand');

      // $check = $this->brands->like('brand_name', $data['brand_name'], 'both')->where('available', 1)->findAll();
      // 여백제거해서도 비교하기 추가할 것
      $check = $this->brands 
                  ->like('brand_name', $data['brand_name'], 'both')
                  ->where('brand_name', $data['brand_name'])
                  ->where('available', 1)->findAll();
      
      if ( empty($check) ) {
        if ( isset($data['supply_rate_by_brand']) ) {
          $data['supply_rate_by_brand'] = round(($data['supply_rate_by_brand'] / 100), 2);
        } else $data['supply_rate_by_brand'] = NULL;

        $data['brand_name'] = addslashes($data['brand_name']);

        $this->brands->insert($data);
        return redirect()->back();
      } else {
        return redirect()->to(base_url('brand'))->withInput()->with('error', '이미 등록되어진 브랜드명');
      }
    }
  }

  public function appliedSupplyRate() {
    $data = $this->request->getVar();
    print_r($data);
    if ( empty($data) ) return redirect()->Back()->with('error', '데이터 처리중 오류 발생');

    $productPrices = $this->products
                      ->select('product.brand_id, product_price.*')
                      ->select('brand_opts.supply_rate_based, brand_opts.supply_rate_by_brand')
                      ->join('brand', 'brand.brand_id = product.brand_id')
                      ->join('brand_opts', 'brand_opts.brand_id = brand.brand_id')
                      ->join('product_price', 'product_price.product_idx = product.id')
                      ->where(['product.brand_id'=> $data['brand_id'], 'product_price.available' => 1])
                      ->findAll();

    if ( !empty($productPrices) ) {
      foreach( $productPrices AS $productPrice) {
        if ( $productPrice['not_calculating_margin'] == 0 ) {
          $supply_price = ($productPrice['retail_price'] * $productPrice['supply_rate_by_brand']);
          $saveCodition = ['idx' => $productPrice['idx']
                          , 'supply_price' => $supply_price];
          
          if ( !$this->productPrice->save($saveCodition) ) {
            return redirect()->back()->with('error', '공급가 계산 중 오류 발생');
          }
        }
      }
    }
  }

  public function appliedMargin() {
    $data = $this->request->getVar();

    if ( empty($data) ) return redirect()->back()->with('데이터 처리중 오류 발생');

    $marginCheck = $this->margin
                        ->select('margin.*')
                        ->select('margin_rate.idx AS margin_rate_idx')
                        ->select('margin_rate.brand_id, margin_rate.margin_rate')
                        ->select('margin_rate.available AS margin_rate_available')
                        ->select('brand_opts.supply_rate_based, brand_opts.supply_rate_by_brand')
                        ->join('margin_rate', 'margin_rate.margin_idx = margin.idx')
                        ->join('brand_opts', '(brand_opts.brand_id = margin_rate.brand_id AND brand_opts.available = 1)', 'left outer')
                        ->where(['margin_rate.brand_id'=> $data['brand_id']
                                , 'margin_rate.available'=> 1])
                        ->orderBy('margin.idx ASC')
                        ->findAll();
    
    if ( !empty($marginCheck) ) {
      $products = $this->products
                        ->where(['brand_id' => $data['brand_id']])
                        ->findAll();

      if ( !empty($products) ) {
        foreach ( $products AS $product ) {
          $productPriceCheck = $this->productPrice
                                    ->where(['product_idx'=> $product['id']
                                          , 'available'=> 1])
                                    ->first();
          // echo "<br/><br/>";
          // var_dump($productPriceCheck);
          // echo "<br/><br/>";
          if ( !empty($productPriceCheck) ) {
            if ( empty($productPriceCheck['not_calculating_margin']) ) {
              if ( !empty($productPriceCheck['supply_price']) ) {
                $supplyPrices = $this->supplyPrice
                                        ->where(['product_price_idx'=> $productPriceCheck['idx']
                                                , 'available' => 1])
                                        ->findAll();

                if ( !empty($supplyPrices) ) {
                  foreach( $supplyPrices AS $supplyPrice ) {
                    foreach($marginCheck AS $margin) {
                      if ( $margin['idx'] == $supplyPrice['margin_idx'] ) {
                        if ( !empty($margin['supply_rate_based']) && !empty($margin['supply_rate_by_brand'])) {
                          if ( $margin['supply_rate_based'] == 1 ) {
                            if ( !empty($productPriceCheck['retail_price']) ) {
                              if ( $productPriceCheck['supply_rate_applied'] == 1 ) {
                                $price = $productPriceCheck['retail_price'] * ($productPriceCheck['supply_rate'] + $margin['margin_rate']);
                              } else {
                                $price = $productPriceCheck['retail_price'] * ($margin['supply_rate_by_brand'] + $margin['margin_rate']);
                              }
                            }
                          }
                        } else {
                          $price = ($productPriceCheck['supply_price'] * $margin['margin_rate']);
                        }
                        $supplyCondition = ['idx' => $supplyPrice['idx']
                                            , 'price' => $price];
                        $this->supplyPrice->save($supplyCondition);
                      }
                    }
                  }
                } else {
                  foreach($marginCheck AS $margin) {
                    if ( !empty($margin['supply_rate_based']) && !empty($margin['supply_rate_by_brand'])) {
                      if ( $margin['supply_rate_based'] == 1 ) {
                        if ( !empty($productPriceCheck['retail_price']) ) {
                          if ( $productPriceCheck['supply_rate_applied'] == 1 ) {
                            $price = $productPriceCheck['retail_price'] * ($productPriceCheck['supply_rate'] + $margin['margin_rate']);
                          } else {
                            $price = $productPriceCheck['retail_price'] * ($margin['supply_rate_by_brand'] + $margin['margin_rate']);
                          }
                        }
                      }
                    } else {
                      $price = ($productPriceCheck['supply_price'] * $margin['margin_rate']);
                    }
                    // echo $price."<br/><br/>";
                    $supplyCondition = ['product_idx' => $productPriceCheck['product_idx']
                                        , 'product_price_idx' => $productPriceCheck['idx']
                                        , 'margin_idx' => $margin['idx']
                                        , 'margin_level' => $margin['margin_level']
                                        , 'price' => $price
                                        , 'available' => 1 ];
                    $this->supplyPrice->save($supplyCondition);
                  }
                }
              } // else return redirect()->back()->with('error', '공급가가 등록되어 있지 않습니다.');
            }
          } else {
            // return redirect()->back()->with('error', '기준할 수 있는 가격 정보가 없습니다.');
          }
        }
      }
    } else {
      // return redirect()->back()->with('error', '브랜드에 margin 값이 활성화 되지 않았습니다.');
    }
  }

  public function addtionalMargin() {
    $brandID = $this->request->uri->getSegment(3);
    $margin = $this->margin->find();

    $lastMargin = end($margin);

    $nextMargin = ['margin_level' => ($lastMargin['margin_level'] + 1)
            , 'margin_section' => chr(ord($lastMargin['margin_section']) + 1)
            , 'available' => 1];

    $brandIds = ( $this->brands->select('brand_id')->where('available', 1)->find() );

    if ( !$this->margin->save($nextMargin) ) {
      return redirect()->back()->with('error', '마진 구간 생성 중 오류 발생');
    } else {
      if ( !empty($brandIds) && !empty($this->margin->getInsertID())) {
        foreach ($brandIds as $key => $value) {
          if ( !$this->marginRate->save(array_merge($value, ['margin_idx' => $this->margin->getInsertID(), 'margin_rate' => 0])) ) {
            return redirect()->back()->with('error', "브랜드 id {$value['brand_id']}의 마진율 생성 중 오류 발생");
          }
        } 
      }
      return redirect()->back()->width('success', "생성 완료");
    }
  }
}
