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
use Status\Config\Status;
use DataFile\Controllers\DataFileController;
use DataFile\Config\DataFile;

class Product extends BaseController {
  public function __construct() {
    helper('data');
    $this->status = config('Status');
    $this->products = new ProductModel();
    $this->brands = new BrandModel();
    $this->brandOpt = new BrandOptModel();
    $this->productSpq = new ProductSpqModel();
    $this->stocks = new StockModel();
    $this->productPrice = new ProductPriceModel();
    $this->supplyPrice = new SupplyPriceModel();
    $this->margin = new MarginModel();

    $this->dataFile = new DataFileController();

    $this->data['header'] = ['css' => ['/table.css', '/product/product.css']
                            ,'js' => ['/product/product.js']];
    $this->data['status'] = $this->status;
  }

  public function index() {
    helper('querystring');
    $data = $this->request->getVar();
    $pageCnt = 10;

    if ( !empty($data['pageCnt']) ) $pageCnt = $data['pageCnt'];
    
    $where = !empty(product_query_return($data)) ? join(' AND ', product_query_return($data)) : [];
  
    $this->data['brands'] = $this->brands->brands()->where('brand.available', 1)->find();

    $this->data['products'] = $this->products
                                ->select('product.*, brand.brand_name')
                                ->select('product_price.idx AS product_price_idx')
                                ->select('product_price.retail_price')
                                ->select('product_price.supply_price')
                                ->select('product_price.supply_rate_applied
                                        , product_price.supply_rate')
                                // ->select('supply_price.idx AS supply_price_idx')
                                // ->select('supply_price.price')
                                ->select('IFNULL(brand_opts.supply_rate_based, 0) AS supply_rate_based')
                                ->select('IFNULL(brand_opts.supply_rate_by_brand, 0) AS supply_rate_by_brand')
                                ->join('brand', 'brand.brand_id = product.brand_id')
                                ->join('brand_opts', 'brand_opts.brand_id = brand.brand_id', 'left outer')
                                ->join('product_price', 'product_price.product_idx = product.id')
                                // ->join('supply_price', 'supply_price.product_idx = product.id', 'left outer')
                                // ->join('(SELECT')
                                ->where($where)
                                ->where('product_price.available', 1)
                                // ->groupBy('supply_price.product_idx')
                                ->orderBy('brand.own_brand DESC')
                                // ->paginate($pageCnt, 'default');
                                ->paginate($pageCnt);
    // echo $this->products->getLastQuery();
    $this->data['pager'] = $this->products->pager;
    return $this->menuLayout('product/main', $this->data);
  }

  public function regist() {
    $controllType = $this->request->uri->getSegment(2);
   
    // $this->data['brands']  = $this->brands->brands()->findAll();
    
    if ($controllType == 'edit') {
      $productId = $this->request->uri->getSegment(3);
      $brandId = $this->request->uri->getSegment(4);
  
      $this->data['edit'] = true;
      $this->data['product'] = $this->brands->brands()
                                  ->select('product.*')
                                  ->join('product', 'brand.brand_id = product.brand_id')
                                  ->where('product.id', $productId)
                                  ->first();
      $this->data['supply'] = $this->productPrice
                                  ->where(['product_price.product_idx'=> $productId, 'product_price.available'=> 1])
                                  ->first();
      $this->data['margin'] = $this->margin
                                  ->select('margin.*')
                                  ->select('margin_rate.idx AS margin_rate_idx')
                                  ->select('margin_rate.brand_id, margin_rate.margin_rate')
                                  ->select('margin_rate.available AS margin_rate_available')
                                  ->select('margin_rate.margin_rate')
                                  ->select('supply_price.idx AS supply_price_idx')
                                  ->select('supply_price.price')
                                  ->select('supply_price.margin_level')
                                  ->join('margin_rate', 'margin_rate.margin_idx = margin.idx AND margin_rate.brand_id = '.$brandId)
                                  // ->join("(SELECT * FROM margin_rate WHERE brand_id = $brandId AND available = 1) AS margin_rate", 'margin_rate.margin_idx = margin.idx', 'left outer')
                                  ->join('supply_price', 'supply_price.margin_idx = margin.idx', 'left outer')
                                  ->where('margin_rate.available', 1)
                                  ->where('supply_price.product_idx', $productId)
                                  ->findAll();
      // if ( empty($this->data['margin']) ) {
      //   $this->data['margin'] = $this->margin
      //                             ->select('margin.*')
      //                             ->select('margin_rate.idx AS margin_rate_idx')
      //                             ->select('margin_rate.brand_id, margin_rate.margin_rate')
      //                             ->select('margin_rate.available AS margin_rate_available')
      //                             ->join("(SELECT * FROM margin_rate WHERE brand_id = $brandId AND available = 1) AS margin_rate", 'margin_rate.margin_idx = margin.idx', 'left outer')
      //                             ->where('margin_rate.available', 1)
      //                             ->findAll();
      // }

    } else {
      $this->data['brands']  = $this->brands->brands()->findAll();
      $this->data['margin'] = $this->margin->findAll();
    }
    

    return $this->menuLayout('product/register', $this->data);
  }

  public function singleRegist() {
    if ( !empty($this->request->getPost()) ) {
      $data = $this->request->getPost();
    } 

    if ( empty($data['product']['brand_id']) ) {      
      return redirect()->back()->withInput()->with('error', '브랜드 선택 안함');
    }

    $brand = $this->brands->where('brand_id', $data['product']['brand_id'])->first();
    if ( empty($brand) ) {
      return redirect()->back()->withInput()->with('error', '해당 브랜드가 없음');
    }

    if ( !empty($data['product']) ) {
      if ( empty($data['product']['id']) ) {
        $prdValidCheck = $this->products
                              // ->like('REPLACE(name_en, \' \', \'\')', preg_replace('/\s+/', '', $data['product']['name_en']), 'both')
                              ->like('UPPER(REPLACE(name_en, \' \', \'\'))', preg_replace('/\s+/', '', strtoupper($data['product']['name_en'])), 'both')
                              ->orWhere(['barcode'=> $data['product']['barcode']])
                              ->where(['productCode' => $data['product']['productCode']])
                              ->first();
        
        if ( empty($prdValidCheck) ) {
          echo "empty<br/>";
          if ( $this->products->insert($data['product']) ) {
            $prdIdx = $this->products->getInsertID();
            $data['product_price']['product_idx'] = $prdIdx;
            
            $validCheck = $this->productPrice->where(['product_idx'=> $prdIdx, 'available' => 1])->first();
            
            if ( !empty($validCheck) ) {
              $this->productPrice->where('idx', $validCheck['idx'])->set('available', 0)->update();
            } else {
              if ( $this->productPrice->insert($data['product_price']) ) {
                $pPriceIdx = $this->productPrice->getInsertID();

                $margins = $this->margin
                                ->join('margin_rate', 'margin_rate.margin_idx = margin.idx')
                                ->where(['margin_rate.available' => 1
                                        , 'margin_rate.brand_id' => $data['product']['brand_id']])
                                ->orderBy('margin.idx ASC')
                                ->findAll();
                if ( !empty($margins) ) {
                  foreach($margins AS $margin) {
                    $this->supplyPrice->save(['product_idx'=> $prdIdx
                                            , 'product_price_idx' => $pPriceIdx
                                            , 'margin_idx' => $margin['margin_idx']
                                            , 'margin_level' => $margin['margin_level']
                                            , 'price' => round(($data['product_price']['supply_price'] * $margin['margin_rate']), 2)]);
                  }
                }
              }
              return redirect()->back()->with('error', '등록 성공');
            }
          }
        // } else {
        //   // if ( $data['product']['edit'] == true ) {
        //   //   if ( !$this->products->save($data['product'])) {
        //   //     return redirect()->back()->withInput()->with('error', '제품 등록중에 오류가 발생했습니다.');
        //   //   }
        //   // }
        }
      } else {
        if ( !$this->products->save($data['product']) ) {
          return redirect()->back()->withInput()->with('error', '제품 등록중에 오류가 발생했습니다.');
        }

        if ( !empty($data['product_price']) ) {
          $this->supplyRateEdit($data['product_price']);
        }
      }
    } else {
      return redirect()->back()->withInput()->with('error', '데이터 전송 중 오류 발생');
    }

    return redirect()->back();
  }

  public function supplyRate() {
    if ( !empty($this->request->getPost()) ) {
      $data = $this->request->getPost('product_price');
    }

    if ( !empty($data) ) {
      foreach( $productPrices as $productPrice ) {
        $this->supplyRateEdit($productPrice);
      }
      return redirect()->back();
    } else return redirect()->back()->withInput()->with('error', '변경할 정보가 없습니다.');
        
  }

  public function supplyRateEdit($data = array()) {
    echo "<br/><br/>";
    print_r($data);
    echo "<br/><br/>";
    if ( !empty($data) ) {
      if ( !isset($data['idx']) ) { // product check
        return redirect()->back()->withInput()->with('error', '수정할 상품이 선택되지 않았습니다.');
      } else {
        if ( !isset($data['supply_rate_applied']) ) {
          $data['supply_rate_applied'] = 0;
          $data['supply_rate'] = NULL;
        }

        if ( empty($data['brand_id']) ) {
          return redirect()->back()->withInput()->with('error', '브랜드 정보가 없습니다');
        } else {
          $brandOpt = $this->brandOpt->where('brand_id', $data['brand_id'])->first();

          if ( !empty($brandOpt) ) {
            $price = $this->productPrice
                              ->where(['product_idx'=> $data['idx']
                                      , 'available' => 1])
                              ->first();

            if ( !empty($data) ) {
              $supply_rate = round(($data['supply_rate'] / 100), 2);
              
              $priceCondition = ['idx' => $price['idx']
                            , 'supply_rate_applied' => $data['supply_rate_applied']
                            , 'supply_rate' => $supply_rate
                            , 'supply_price' => ($price['retail_price'] * $supply_rate)
                            , 'available' => 1];
              if ( !$this->productPrice->save($priceCondition) ) {
                // $supplyPrice = $this->supplyPrice
                //                     ->where(['product_idx' => $data['idx']
                //                             , 'product_price_idx' => $data['product_price_idx']
                //                             , 'available' => 1])
                //                     ->first();
                
                // if ( !empty($supplyPrice) ) {
                //   $this->supplyPrice
                //         ->where('idx', $supplyPrice['idx'])
                //         ->set('price', ($price['retail_price'] * $supply_rate))
                //         ->update();
                // }
              }
            }
          } else {
            if ( isset($data['not_calculating_margin']) && $data['not_calculating_margin'] == 1) {
              echo $data['product_price_idx'];
              $this->productPrice->save(['idx' => $data['product_price_idx']
                                        , 'not_calculating_margin' => $data['not_calculating_margin']]);

              if ( strtolower(gettype($data['price'])) == 'array' ) {
                foreach( $data['price'] AS $price ) {
                  $price['product_price_idx'] = $data['product_price_idx'];
                  $price['product_idx'] = $data['idx'];

                  if ( isset($price['supply_price_idx']) ) {
                    $price['idx'] = $price['supply_price_idx'];
                    $this->supplyPrice->save($price);
                  }
                }
              }
            } else {
              $this->productPrice->save(['idx' => $data['product_price_idx']
                                        , 'not_calculating_margin' => 0]);

              if ( strtolower(gettype($data['price'])) == 'array' ) {
                foreach( $data['price'] AS $price ) {
                  $price['product_price_idx'] = $data['product_price_idx'];
                  $price['product_idx'] = $data['idx'];
                  $price['price'] = ($data['supply_price'] * $price['margin_rate']);

                  if ( isset($price['supply_price_idx']) ) {
                    $price['idx'] = $price['supply_price_idx'];
                    $this->supplyPrice->save($price);
                  }
                }
              }
            }
            // return redirect()->back()->withInput()->with('error', '브랜드별 공급률 설정이 되지 않았습니다.');
          }
        }
      }
    }
  }

  public function attachProduct() {
    $validationRule = [
      // 'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv],'
      'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv,xls,xlsx],'
    ];
    $brand_id = NULL;

    if ( !$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    if ( empty($this->request->getVar('brand_id')) ) {
      return redirect()->back()->withInput()->with('error', '브랜드 선택 안함');
    } else $brand_id = $this->request->getvar('brand_id');

    if ( $this->request->getFile('file') ) {
      $file = $this->request->getFile('file');      
      $data = $this->dataFile->attachData($file);

      $fields = array_merge( $this->status->getHeader('product')['export']
                                  , $this->status->getHeader('supplyPrice')['export']
                                  , $this->status->getHeader('productSpq')['export']);
      array_unshift($fields, ['header'=> 'id', 'field' => 'id'], ['header' => 'Brand ID', 'field' => 'brand_id']);

      if ( !empty($data) ) {
        // $brandCheck = $this->brands->where('brand_id', $brand_id)->first();
        // if ( empty($brandCheck) ) {
        //   return redirect()->back()->with('error', '해당하는 브랜드가 없습니다.');
        // }

        $numberOfRecords = $this->dataFile->numberOfRecords;
        $numberOfFields = $this->dataFile->numberOfFields;
        $productArr = array();
        $productPriceArr = array();
        $successCnt = 0;
        $failCnt = 0;
        
        $contents = array_splice($data, 1, $numberOfFields);        
        foreach($contents AS $i => $fileData) :
          for ( $j = 0; $j < $numberOfRecords; $j++) :
            // if ( ($j >= 0 && $j < 27) && $j != 2 ) {
            if ( ($j >= 0 && $j < 27) ) {
              $productArr[$i][$fields[$j]['field']] = $fileData[$j];
            }

            if ( ($j > 26 && $j < 34) ) {
              $productPriceArr[$i][$fields[$j]['field']] = $fileData[$j];
            }

            if ( $j > 33 && $j <= $numberOfRecords ) {
              $productSpqArr[$i][$fields[$j]['field']] = $fileData[$j];
            }
          endfor;
        endforeach;

        // var_dump($productArr);
        // var_dump($productPriceArr);
        // var_dump($productSpqArr);

        if ( !empty($productArr) ) :
          foreach($productArr AS $prd_i => $product ) :
            if ( is_null($product['box']) ) $product['box'] = 0;
            if ( is_null($product['in_the_box']) ) $product['in_the_box'] = 0;
            if ( is_null($product['container']) ) $product['container'] = 0;
            if ( is_null($product['discontinued']) ) $product['discontinued'] = 0;
            if ( is_null($product['display']) ) $product['display'] = 0;
            if ( is_null($product['shipping_weight']) ) $product['shipping_weight'] = 0;
            if ( is_null($product['type']) ) $product['type'] = '';
            if ( is_null($product['type_en']) ) $product['type_en'] = '';
            if ( is_null($product['name']) ) $product['name'] = '';
            if ( is_null($product['name_en']) ) $product['name_en'] = '';
            if ( is_null($product['productCode']) ) $product['productCode'] = 0;

            if ( $brand_id != $product['brand_id'] ) {
              $brandValidCheck = $this->brands->where('brand_id', $product['brand_id'])->first();

              if ( empty($brandValidCheck) ) {
                return redirect()->back()->with('error', '해당하는 브랜드가 없습니다.');
              } else {
                if ( $brandValidCheck['brand_name'] != $product['brand'] ) {
                  return redirect()->back()->with('error', '등록하고자 하는 상품의 브랜드명이 일치하지 않습니다.');
                } else unset($product['brand']);
              }
            }

            if ( !empty($product['id']) || !is_null($product['id'])) :
              $productPriceArr[$prd_i]['product_idx'] = $product['id'];
              $productSpqArr[$prd_i]['product_idx'] = $product['id'];

              $prdInfo = $this->products->where(['barcode' => trim($product['barcode']), 'id' => $product['id']])->first();

              if ( empty($prdInfo) ) {
                $this->products->save($product);
              } else {
                if ( $prdInfo['id'] == $product['id'] ) {
                  $this->products->save(['id'=> $product['id'], 'img_url' => $product['img_url']]);  
                }
                // var_dump($prdInfo);
                // // if ( $prdInfo['name_en'] != $product['name_en'] ) {
                // //   // print_r($prdInfo);
                // //   // 이름이 다르면 다른 제품으로 판단할지 체크하기
                // // }
                var_dump($product);
                var_dump($prdInfo);
              }
            else :
              $prdInfo = $this->products
                              ->where(['REPLACE(barcode, " ", "")' => str_replace(" ", "", $product['barcode'])
                                      , 'brand_id' => $brand_id
                                      , 'REPLACE(name, " ", "")' => str_replace(" ", "", $product['name'])
                                      , 'UPPER(REPLACE(name_en, " ", ""))' => str_replace(" ", "", strtoupper($product['name_en']))
                                      , 'REPLACE(type, " " ,"")' => str_replace(" ", "", $product['type'])
                                      , 'UPPER(REPLACE(type_en, " ", ""))' => str_replace(" ", "", strtoupper($product['type_en']))])
                              ->first();

              if ( empty($prdInfo) ) {
                $product['name'] = addslashes($product['name']);
                $product['name_en'] = addslashes($product['name_en']);
                $product['spec'] = addslashes($product['spec']);
                $product['spec2'] = addslashes($product['spec2']);
                $product['type'] = addslashes($product['type']);
                $product['type_en'] = addslashes($product['type_en']);

                if ($this->products->save($product)) {
                  $productPriceArr[$prd_i]['product_idx'] = $this->products->getInsertID();
                  $productSpqArr[$prd_i]['product_idx'] =  $this->products->getInsertID();
                }
              } else {
                $productPriceArr[$prd_i]['product_idx'] = $prdInfo['id'];
                $productSpqArr[$prd_i]['product_idx'] = $prdInfo['id'];
              }
            endif;
          endforeach;

          // if ( !empty($productPriceArr) ) :
          //   // var_dump($productPriceArr);
          //   $marginRateModel = new MarginRateModel();
          //   $supplyPriceArr = [];
          //   $brand_id = NULL;
            
          //   $margins = $this->margin->where('available', 1)->findAll();
          //   if ( !empty($margins) ) {
          //     foreach($productPriceArr AS $price_i => $price ) :
          //       $priceTemp = [];
          //       $product_price_idx = NULL;

          //       if ( is_null($price['retail_price']) ) $price['retail_price'] = 0;
          //       if ( is_null($price['supply_rate_applied']) ) $price['supply_rate_applied'] = 0;
          //       if ( is_null($price['supply_rate']) ) $price['supply_rate'] = 0;
          //       if ( is_null($price['not_calculating_margin']) ) $price['not_calculating_margin'] = 0;
          //       if ( is_null($price['taxation']) ) $price['taxation'] = 0;

          //       if ( !empty($price['brand_id']) && is_null($brand_id) ) $brand_id = $price['brand_id'];

          //       if ( !empty($price['price']) ) {
          //         $priceTemp = explode('/', $price['price']);
          //         unset($price['price']);
          //       }

          //       $prdPrice = $this->productPrice->where(['product_idx' => $price['product_idx']
          //                                               , 'retail_price' => $price['retail_price']
          //                                               , 'supply_price'  => $price['supply_price']
          //                                               , 'supply_rate_applied' => $price['supply_rate_applied']
          //                                               , 'supply_rate' => $price['supply_rate']])
          //                                       ->first();
          //       if ( empty($prdPrice) ) {
          //         $price['available'] = 1;
          //         if ( $this->productPrice->save($price) ) {
          //           $product_price_idx = $this->productPrice->getInsertID();
          //         }
          //       } else {
          //         if ( $prdPrice['available'] == 0 ) {
          //           $availiableCheck = $this->productPrice->where(['product_idx' => $price['product_idx'], 'available' => 1])->findAll();
          //           if ( !empty($availiableCheck) ) {
          //             foreach($availiableCheck AS $i => $available) {
          //               if ($this->productPrice->save(['idx' => $available['idx'], 'available' => 0]) ) {
          //                 unset($availiableCheck[$i]);
          //               } else {
          //                 return redirect()->back()->with('error', 'product price update error');
          //               }
          //             }
          //           }
          //           // if ( empty($availiableCheck) ) $this->productPrice->save(['idx'=> $prdPrice['idx'], 'available' => 1]);
          //           if ( $this->productPrice->save(['idx'=> $prdPrice['idx'], 'available' => 1]) ) {
          //             $product_price_idx = $this->productPrice->getInsertID();
          //           } else {
          //             return redirect()->back()->with('error', 'product price update error 2');
          //           }
          //         } else {
          //           $product_price_idx = $prdPrice['idx'];
          //         }
          //       }

          //       if ( !empty($product_price_idx) ) {
          //         if ( !empty($priceTemp) ) {
          //           foreach($margins AS $i => $margin) {
          //             array_push($supplyPriceArr, ['margin_idx' => $margin['idx']
          //                                         , 'margin_level' => $margin['margin_level']
          //                                         , 'price' => $priceTemp[$i]
          //                                         , 'product_idx' => $price['product_idx']
          //                                         , 'product_price_idx' => $product_price_idx]);
                      
          //           }
          //         }
          //       } else {
          //         return redirect()->back()->with('error', 'supply price 입력중 오류 발생. 다시 시도해주세요.');
          //       }
          //     endforeach;
          //   } else {
          //     // margin 정보가 없을 때 오류 처리하기.
          //   return;
          //   }
          // else :
          //   // productpricearr empty
          //   return;
          // endif;

          // if ( !empty($supplyPriceArr)) :
          //   foreach($supplyPriceArr AS $supplyPrice) :
          //     $supplyPriceVaildCheck = $this->supplyPrice
          //                                   ->where(['product_idx' => $supplyPrice['product_idx']
          //                                           , 'product_price_idx' => $supplyPrice['product_price_idx']
          //                                           , 'margin_idx' => $supplyPrice['margin_idx']
          //                                           , 'margin_level' => $supplyPrice['margin_level']
          //                                           , 'price' => $supplyPrice['price']])
          //                                   ->first();
          //     if ( empty($supplyPriceVaildCheck) ) {
          //       $supplyPrice['available'] = 1;
          //       if (!$this->supplyPrice->save($supplyPrice) ) {
          //         return redirect()->back()->with('error', '가격 등록중에 오류가 밸생했습니다. 다시 확인 후에 시도해주세요.');
          //       }
          //     } else {
          //       if ( $supplyPriceVaildCheck['available'] == 0 ) {
          //         $availableCheck = $this->supplyPrice
          //                                 ->where(['product_idx' => $supplyPrice['product_idx']
          //                                         , 'product_price_idx' => $supplyPrice['product_price_idx']
          //                                         , 'margin_idx' => $supplyPrice['margin_idx']
          //                                         , 'available' => 1])
          //                                 ->findAll();
          //         if ( !empty($availableCheck) ) {
          //           foreach($availableCheck AS $checkValue ) {
          //             if ( !$this->supplyPrice->save(['idx' => $checkValue['idx'], 'available' => 0]) ) {
          //               return redirect()->back()->with('error', 'supply price update error');
          //             }
          //           }
          //         }

          //         if ( !$this->supplyPrice->save(['idx' => $supplyPriceVaildCheck['idx'], 'available' => 1]) ) {
          //           return redirect()->back()->with('error', 'supply price update error 2');
          //         }
          //       }
          //     }
          //   endforeach;
          // else : 
          //   // supply price arr is empty 
          // endif;

          // if ( !empty($productSpqArr) ) :
          //   // var_dump($productSpqArr);
          //   foreach($productSpqArr AS $productSpq) :
          //     if ( empty($productSpq['product_idx']) ) {
          //       return redirect()->back()->with('error', 'sqp입력 중 오류 발생');
          //     } else {
          //       if ( is_null($productSpq['moq']) ) $productSpq['moq'] = 10;
          //       if ( is_null($productSpq['spq_inBox']) ) $productSpq['spq_inBox'] = 10;
          //       if ( is_null($productSpq['spq_outBox']) ) $productSpq['spq_outBox'] = 10;
          //       if ( is_null($productSpq['spq']) ) $productSpq['moq'] = 2;
          //       if ( is_null($productSpq['calc_code']) ) $productSpq['calc_code'] = 0;
          //       if ( is_null($productSpq['calc_unit']) ) $productSpq['calc_unit'] = 10;

          //       $valideCheck = $this->productSpq->where(['product_idx' => $productSpq['product_idx'], 'available' => 1])->first();
          //       if ( empty($valideCheck) ) {
          //         $productSpq['available'] = 1;
          //         if (!$this->productSpq->save($productSpq)) {
          //           return redirect()->back()->with('error', 'spq inser error');
          //         }
          //       } else {
          //         $productSpq['available'] = 1;
          //         $productSpq['id'] = $valideCheck['id']; 
          //         if (!$this->productSpq->save($productSpq)) {
          //           return redirect()->back()->with('error', 'spq inser error');
          //         }
          //       }
          //     }
          //   endforeach;
          // endif;

          // return redirect()->back()->with('error', '등록 완료');
        endif;
      }
    }
  }

  public function exportData() {
    // $fileType = 'csv';
    $brandId = (int) $this->request->uri->getSegment(3);
    $products = [];
    $brandInfo;
    $fileName = NULL;

    if ( !empty($this->request->getPost()) ) {
      $data = $this->request->getPost();

      if ( empty($brandId) ) {
        if ( !empty($data['brand_id']) ) {
          $brandId = $data['brand_id'];
        }
      }
    }

    if ( !empty($brandId) ) {
      $brandInfo = $this->brands->where('brand_id', $brandId)->first();
      $this->products->where('product.brand_id', $brandId);

      $fileName = $brandInfo['brand_name']."_".date('Ymd_his');
    } else $fileName = 'BeautynetKorea_'.date('Ymd_his');
    
    $this->dataFile->exportOptions( [ 'width' => 15 ], 
                                    [ 'bold'  =>  true, 
                                      'fill'  =>  ['color' => 'FFF5DEB3'], 
                                      'align_vertical'  =>  'center',
                                      'set_wrap'  =>  true,
                                      'colCnt' => 2, 
                                      'colName' => ['header', 'field']]);
    $header = array_merge($this->status->getHeader('product')['export']
                        , $this->status->getHeader('supplyPrice')['export']
                        , $this->status->getHeader('productSpq')['export']);

    array_unshift($header
                        , ['header' => 'ID', 'field' => 'id', 'opts' => ['width' => 8]]
                        , ['header' => 'Brand ID', 'field' => 'brand_id', 'opts' => ['width' => 8]]);
                        
    if ( $data['prd-include'] == true ) {
      $products = $this->products
                    ->select("product.id")
                    ->select("brand.brand_id, UPPER(brand.brand_name) AS brand_name")
                    ->select("product.barcode, product.productCode, product.img_url")
                    ->select("product.name")
                    ->select("product.name_en")
                    ->select('product.box, product.in_the_box, product.contents_of_box')
                    ->select("product.spec, product.spec2, product.container, product.spec_detail, product.spec_pcs")
                    ->select("product.shipping_weight, product.sample")
                    ->select("product.type, product.type_en, product.package, product.package_detail")
                    ->select("product.renewal, product.etc")
                    ->select("product.discontinued, product.display")
                    ->select("product_price.retail_price")
                    ->select('product_price.supply_price')
                    ->select("IFNULL ( product_price.supply_rate_applied, 0 ) AS supply_rate_applied")
                    ->select("IFNULL ( product_price.supply_rate, '0.00' ) AS supply_rate")
                    ->select('product_price.not_calculating_margin')
                    ->select(' IF (product_price.not_calculating_margin = 1, supply_price.price, "") AS price')
                    ->select("product_price.taxation")
                    ->select('product_spq.moq, product_spq.spq_inBox, product_spq.spq_outBox, product_spq.spq,
                              product_spq.calc_code, product_spq.calc_unit')
                    ->join("brand", "brand.brand_id = product.brand_id")
                    ->join("brand_opts", "brand_opts.brand_id = brand.brand_id", 'left outer')
                    ->join('product_price', "product_price.product_idx = product.id", 'left outer')
                    ->join('( SELECT product_idx, GROUP_CONCAT(price SEPARATOR "/") AS price
                              FROM supply_price
                              WHERE available = 1
                              GROUP BY product_idx ) AS supply_price'
                            , 'supply_price.product_idx = product_price.product_idx'
                            , 'left outer')
                    ->join('product_spq', 'product_spq.product_idx = product.id AND product_spq.available = 1', 'left outer')
                    // ->where(['product.discontinued' => 0, 'product.display' => 1])
                    ->where('product_price.available', 1)
                    ->orderBy('brand.brand_id ASC, brand.own_brand DESC, product.id ASC')
                    ->findAll();
      // echo $this->products->getLastQuery();
      // if ( !empty($products) ) {
      //   $fileName = $products[0]['brand_name'].'_'.date('Ymd_his');
      // }

      if ( empty($products) && !empty($brandId) ) {
        $products = $this->brands
                      ->select("'' AS id, brand_id, brand_name")
                      ->where('brand_id', $brandId)->findAll();
      }
    }
    $this->dataFile->exportData($header, $products, $fileName, 'xls');
  }

  public function attachProductSupplyPrice() {
    $validationRule = [
      'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv,xlsx,xls],'
    ];

    if ( !$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
   
    if ( $this->request->getFile('file') ) {
      $file = $this->request->getFile('file');
      $data = $this->dataFile->attachData($file);
      $priceFields = $this->status->getHeader('supplyPrice')['fields'];

      if ( !empty($data) ) {

      }
      // if ( $file->isValid() && !$file->hasMoved() ) {
      //   $newName = $file->getRandomName();
      //   $file->move('../public/csvfile', $newName);
      //   $file = fopen('../public/csvfile/'.$newName, 'r');
      //   $i = 0;
      //   $csvArr = array();
      //   $numberOfFields = 9;
        
      //   while( ($filedata = fgetcsv($file, 1000, ",")) !== FALSE ) {
      //     $num = count($filedata);
      //     echo $num."<br/>";
      //     print_r($filedata);
      //     if ( $i > 0 && $num == $numberOfFields ) {
      //       $csvArr[$i]['product_idx'] = trim($filedata[0]);
      //       $csvArr[$i]['retail_price'] = trim($filedata[6]);
      //       $csvArr[$i]['price'] = trim($filedata[7]);
      //       $csvArr[$i]['taxation'] = trim($filedata[8]);
      //       // $csvArr[$i]['available'] = trim();
      //     }
      //     $i++;
      //   }
      //   fclose($file);

      //   print_r($csvArr);

      //   $count = 0;
      //   foreach($csvArr as $userdata) {
      //     $findProduct = $this->productPrice
      //                       ->where('product_idx', $userdata['product_idx'])
      //                       ->where('available', 1)
      //                       ->countAllResults();
              
      //     if ( $findProduct == 0 ) {
      //       $userdata['available'] = 1;
      //       if ( $this->productPrice->insert($userdata)) { 
      //         $productId = $this->productPrice->getInsertID();
      //         $count++;
      //       }
      //     }
      //   }
      //   // echo $this->productPrice->getLastQuery();
      //   session()->setFlashdata('message', $count.' rows successfully added');
      //   session()->setFlashdata('alert-class', 'alert-success');
      // } else {
      //   session()->setFlashdata('message', 'CSV file could not be imported');
      //   session()->setFlashdata('alert-class', 'alert-danger');
      // }
    } else {
      session()->setFlashdata('message', 'CSV file could not be imported');
      session()->setFlashdata('alert-class', 'alert-danger');
    }

    return redirect()->back()->withInput();
  }

  public function attachProductSpq() {
    $validationRule = [
      'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv,xls,xlsx],'
    ];

    if ( !$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
   
    if ( $file = $this->request->getFile('file') ) {
      if ( $file->isValid() && !$file->hasMoved() ) {
        $newName = $file->getRandomName();
        $file->move('../public/csvfile', $newName);
        $file = fopen('../public/csvfile/'.$newName, 'r');
        $i = 0;
        $csvArr = array();
        $numberOfFields = 10;
        
        while( ($filedata = fgetcsv($file, 1000, ",")) !== FALSE ) {
          $num = count($filedata);
          if ( $i > 0 && $num == $numberOfFields ) {
            $csvArr[$i]['product_idx'] = trim($filedata[0]);
            $csvArr[$i]['moq'] = trim($filedata[7]);
            $csvArr[$i]['spq_inBox'] = trim($filedata[8]);
            $csvArr[$i]['spq_outBox'] = trim($filedata[9]);
          }
          $i++;
        }
        fclose($file);

        print_r($csvArr);

        $count = 0;
        foreach($csvArr as $userdata) {
          // $findProduct = $this->productSpq
          //                   ->where('product_idx', $userdata['product_idx'])
          //                   ->where('available', 1)
          //                   ->countAllResults();
              
          // if ( $findProduct == 0 ) {
            $userdata['available'] = 1;
            if ( $this->productSpq->insert($userdata)) { 
              $productId = $this->productSpq->getInsertID();
              $count++;
            }
          // }
        }
        // echo $this->productSpq->getLastQuery();
        session()->setFlashdata('message', $count.' rows successfully added');
        session()->setFlashdata('alert-class', 'alert-success');
      } else {
        session()->setFlashdata('message', 'CSV file could not be imported');
        session()->setFlashdata('alert-class', 'alert-danger');
      }
    } 

    return redirect()->back()->withInput();
  }

  public function attachStocks() { // 수정하기. stocks detail에 입고일에 이미 들어온게 있는지 확인. available = 1 이고 (supplied qty - salse qty - pending qty) > 0 한 것의 합계 넣어주기
    $validationRule = [
      'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv],'
    ];

    if ( !$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    if ( $file = $this->request->getFile('file') ) {
      if ( $file->isValid() && !$file->hasMoved() ) {
        $newName = $file->getRandomName();
        $file->move('../public/csvfile', $newName);
        $file = fopen('../public/csvfile/'.$newName, 'r');
        $i = 0;
        $csvArr = array();
        $numberOfFields = 7;
        
        while( ($filedata = fgetcsv($file, 1000, ",")) !== FALSE ) {
          $num = count($filedata);
          if ( $i > 0 && $num == $numberOfFields ) {
            $csvArr[$i]['product_idx'] = trim($filedata[0]);
            $csvArr[$i]['spq'] = trim($filedata[6]);
          }
          $i++;
        }
        fclose($file);

        print_r($csvArr);

        $count = 0;
        foreach($csvArr as $userdata) {
          $findProduct = $this->productSpq
                            ->where('product_idx', $userdata['product_idx'])
                            ->where('available', 1)
                            ->countAllResults();
              
          if ( $findProduct == 0 ) {
            $userdata['available'] = 1;
            if ( $this->productSpq->insert($userdata)) { 
              $productId = $this->productSpq->getInsertID();
              $count++;
            }
          }
        }
        // echo $this->productSpq->getLastQuery();
        session()->setFlashdata('message', $count.' rows successfully added');
        session()->setFlashdata('alert-class', 'alert-success');
      } else {
        session()->setFlashdata('message', 'CSV file could not be imported');
        session()->setFlashdata('alert-class', 'alert-danger');
      }
    } 

    return redirect()->back()->withInput();

  }
}
