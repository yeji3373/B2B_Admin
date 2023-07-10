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

      if ( $data['supply_rate_control'] == true ) {
        if ( !empty($data['brand_opt']) ) {
          $brandOpt = $data['brand_opt'][$data['brand_id']];
          $brandOpt['brand_id'] = $data['brand_id'];

          // $brandOptCheck = $this->brandOpt->where(['brand_id'=> $brandOpt['brand_id'], 'available' => 1])->first();
          // if ( !empty($brandOptCheck) ) {
          //   $this->brandOpt->save(['idx'=> $brandOpt['idx'], 'available'=> 0]);
          // }
          
          if ( !empty($brandOpt['supply_rate_by_brand']) ) {
            $brandOpt['supply_rate_by_brand'] = round(($brandOpt['supply_rate_by_brand'] / 100), 2);
            $brandOpt['available'] = 1;
          } else {
            $brandOpt['available'] = 0;
          } 
          if ( !$this->brandOpt->save($brandOpt) ) {
            return redirect()->back()->withInput()->with('error', '공급률 수정 중 오류');
          }

          if ( $brandOpt['available'] == 1 ) {
            // 차후에 공급률 미적용을 선택시, confirm(현재 공급률 유지?) Yes일 때만
            $this->appliedSupplyRate();
          } else { 
            $supplyRateAppliedCheck = $this->productPrice
                                          ->join('product', 'product.id = product_price.product_idx')
                                          ->where(['brand_id'=> $brandOpt['brand_id']
                                                , 'supply_rate_applied' => 1])
                                          ->findAll();
            
            if ( !empty($supplyRateAppliedCheck) ) {
              foreach( $supplyRateAppliedCheck AS $applied ) {
                $this->productPrice->save(['idx' => $applied['idx']
                                          , 'supply_rate_applied' => 0
                                          , 'supply_rate' => 0]);
              }
            }
          }
          unset($data['brand_opt'][$data['brand_id']]);
        }
      }

      if ( $data['margin_rate_control'] == true ) {
        if ( !empty($data['margin_rate']) ) {
          foreach($data['margin_rate'] as $marginRate ) {
            if ( !isset($marginRate['available']) ) $marginRate['available'] = 0;
            if ( isset($marginRate['margin_rate'] ) ) $marginRate['margin_rate'] = ($marginRate['margin_rate'] / 100);

            if ( !$this->marginRate->save($marginRate) ) {
              return redirect()->back()->withInput()->with('error', '수정 중 오류');
            }
            unset($data['margin_rate']);
          }          
          $this->appliedMargin();
        }
      }

      if ( !empty($data['brand']) ) {
        $brand = $data['brand'][$data['brand_id']];
        $brand['brand_id'] = $data['brand_id'];
        $brand['brand_name'] = addslashes($brand['brand_name']);

        if ( !$this->brands->save($brand) ) {
          return redirect()->back()->withInput()->with('error', '브랜드 수정 중 오류');
        }
      }
      
      if ( $data['margin_rate_control'] == true || $data['supply_rate_control'] == true) {
        $this->cart->where('brand_id', $data['brand_id'])->set('supply_price_changed', 1)->update();
      }

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
          // print_r($productPriceCheck);
          // echo "<br/><br/>";
          if ( !empty($productPriceCheck) ) {
            if ( empty($productPriceCheck['not_calculating_margin']) ) { // not_calculating_margin == 0
              if ( !empty($productPriceCheck['supply_price']) ) {
                $supplyPrices = $this->supplyPrice
                                        ->where(['product_price_idx'=> $productPriceCheck['idx']
                                                , 'available' => 1])
                                        ->findAll();
                if ( !empty($supplyPrices) ) {
                  foreach($supplyPrices AS $supplyPrice ) {
                    foreach($marginCheck AS $margin) {
                      if ( $margin['idx'] == $supplyPrice['margin_idx'] ) {
                        if ( !empty($margin['supply_rate_based']) && !empty($margin['supply_rate_by_brand'])) {
                          if ( $margin['supply_rate_based'] == 1 ) {
                            if ( $productPriceCheck['supply_rate_applied'] == 1 ) {
                              $price = $productPriceCheck['retail_price'] * ($productPriceCheck['supply_rate'] + $margin['margin_rate']);
                            } else {
                              $price = $productPriceCheck['retail_price'] * ($margin['supply_rate_by_brand'] + $margin['margin_rate']);
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
                        if ( $productPriceCheck['supply_rate_applied'] == 1 ) {
                          $price = $productPriceCheck['retail_price'] * ($productPriceCheck['supply_rate'] + $margin['margin_rate']);
                        } else {
                          $price = $productPriceCheck['retail_price'] * ($margin['supply_rate_by_brand'] + $margin['margin_rate']);
                        }
                      }
                    } else {
                      $price = ($productPriceCheck['retail_price'] * $margin['margin_rate']);
                    }
                    echo $price."<br/><br/>";
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
}
