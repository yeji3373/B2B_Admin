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

    if ( !$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    if ( empty($this->request->getVar('brand_id')) ) {
      return redirect()->back()->withInput()->with('error', '브랜드 선택 안함');
    }

    if ( $this->request->getFile('file') ) {
      $file = $this->request->getFile('file');      
      $data = $this->dataFile->attachData($file);

      $productFileds = $this->status->getHeader('product')['fields'];
      // array_unshift($productFileds, 'brand_id', 'id');
      array_unshift($productFileds, 'id');

      $fields = array_merge($productFileds, $this->status->getHeader('supplyPrice')['fields']);
      $fields = array_merge($fields, $this->status->getHeader('productSpq')['fields']);

      if ( !empty($data) ) {
        $numberOfRecords = $this->dataFile->numberOfRecords;
        // $numberOfFields = $this->dataFile->numberOfFields;
        $productArr = array();
        $productPriceArr = array();
        $successCnt = 0;
        $failCnt = 0;
        
      //   $brandCheck = $this->brands->where('brand_id', $this->request->getVar('brand_id'))->first();

      //   if ( !empty($brandCheck) ) {
      //     $data = $this->dataFile->specificFiltering($data, 4, $brandCheck['brand_name']);
      //   } else {
      //     return redirect()->back()->with('error', '해당하는 브랜드가 없습니다.');
      //   }

      //   foreach($data AS $i => $fileData) {
      //     array_unshift($fileData, $this->request->getVar('brand_id'));
      //     for($j = 0; $j < $numberOfRecords; $j++ ) {
      //       if ( count($fields) > $j ) {
      //         if ( count($productFileds) > $j ) {
      //           $productArr[$i][$fields[$j]] = $fileData[$j];
      //         } else {
      //           if ( count($productFileds) == $j ) {
      //             $productPriceArr[$i][$fields[$j]] = $fileData[0];
      //           }                 
      //           $productPriceArr[$i][$fields[$j + 1]] = $fileData[$j];
      //         }
      //       }
      //     }
      //   }

      //   if ( !empty($productArr) ) {
      //     foreach($productArr AS $i => $productData ) {
      //       if ( !empty($productData['id']) ) {
      //         unset($productData['barcode']);
      //         unset($productData['name']);
      //         unset($productData['type']);
      //       }
      //       $this->products->save($productData);
      //       if ( $this->products->getInsertID() ) {
      //         $successCnt++;
      //         $productPriceArr[$i]['product_idx'] = $this->products->getInsertID();
      //       }
      //     }
      //   }

      //   if ( !empty($productPriceArr) ) {
      //     foreach ( $productPriceArr AS $productPrice ) {
      //       $productChk = $this->productPrice
      //                         ->where(['product_idx' => $productPrice['product_idx']
      //                                 , 'available' => 1 ])
      //                         ->first();
            
      //       if ( !empty($productChk) ) {
      //         $productPriceIdx = $productChk['idx'];

      //         $this->productPrice->save(['idx' => $productPriceIdx
      //                                   , 'retail_price' => $productPrice['retail_price']
      //                                   , 'supply_price' => $productPrice['supply_price']
      //                                   , 'supply_rate_applied' => $productPrice['supply_rate_applied']
      //                                   , 'supply_rate' => $productPrice['supply_rate']
      //                                   , 'not_calculating_margin' => $productPrice['not_calculating_margin']]);
      //       } else {
      //         $this->productPrice->save(['product_idx' => $productPrice['product_idx']
      //                                   , 'retail_price' => $productPrice['retail_price']
      //                                   , 'supply_price' => $productPrice['supply_price']
      //                                   , 'supply_rate_applied' => $productPrice['supply_rate_applied']
      //                                   , 'supply_rate' => $productPrice['supply_rate']
      //                                   , 'not_calculating_margin' => $productPrice['not_calculating_margin']
      //                                   , 'available' => 1]);

      //         $productPriceIdx =$this->productPrice->getInsertID();
      //       }

      //       if (!empty($productPriceIdx) && !empty($productPrice['price'])) {
      //         $tempPrice = explode('/', $productPrice['price']);
      //         $supplyPriceCheck = $this->supplyPrice
      //                                   ->where(['product_idx'=> $productPrice['product_idx']
      //                                           , 'product_price_idx' => $productPriceIdx
      //                                           , 'available' => 1])
      //                                   ->orderBy('margin_level ASC')
      //                                   ->findAll();
      //         if ( !empty($supplyPriceCheck) ) {
      //           print_r($supplyPriceCheck);
      //           foreach($supplyPriceCheck AS $i => $supplies ) {
      //             echo $tempPrice[$i]."<br/>";
      //             echo $supplies['price']."<br/>";
      //             if ( $tempPrice[$i] != $supplies['price'] ) {
      //               $this->supplyPrice->save(['idx'=> $supplies['idx']
      //                                       , 'price'=> $tempPrice[$i]]);
      //             }
      //           }
      //         } else {
      //           $marginCheck = $this->margin
      //                             ->join('margin_rate', 'margin_rate.margin_idx = margin.idx')
      //                             ->where('brand_id', $this->request->getVar('brand_id'))
      //                             ->where('margin.available', 1)
      //                             ->orderBy('margin_level ASC')
      //                             ->findAll();

      //           if ( !empty($marginCheck) ) {
      //             foreach($marginCheck AS $i => $margin) {
      //             $this->supplyPrice->save(['product_price_idx' => $productPriceIdx
      //                                     , 'product_idx' => $productPrice['product_idx']
      //                                     , 'margin_idx' => $margin['margin_idx']
      //                                     , 'margin_level' => $margin['margin_level']
      //                                     , 'price' => $tempPrice[$i]
      //                                     , 'available' => 1]);
      //             }
      //           }
      //         }
      //       }
      //     }
      //   }


      //   // echo count($productArr)."<br/>";
      //   // print_r($productArr);
      //   // echo "<br/><Br/>";
      //   // echo count($productPriceArr)."<br/>";
      //   // print_r($productPriceArr);
      //   // echo "<br/><Br/>";
      //   return redirect()->back()->with('error', '등록 완료');
      // } else {
      //   return redirect()->back()->with('error', '등록할 데이터가 없습니다');
      }
    }
  }

  public function exportData() {
    // $fileType = 'csv';
    $brandId = (int) $this->request->uri->getSegment(3);
    $products = [];

    if ( !empty($this->request->getPost()) ) {
      $data = $this->request->getPost();

      if ( empty($brandId) ) {
        if ( !empty($data['brand_id']) ) {
          $brandId = $data['brand_id'];
        }
      }
    }

    if ( !empty($brandId) ) {
      $this->products->where('product.brand_id', $brandId);
    }
    
    // $header = array_merge($this->status->getHeader('product')['header']
    //                     , $this->status->getHeader('supplyPrice')['header']
    //                     , $this->status->getHeader('productSpq')['header']);
    $this->dataFile->exportOptions( [ 'width' => 15 ], 
                                    [ 'bold'  =>  true, 
                                      'fill'  =>  ['color' => 'FFF5DEB3'], 
                                      'align_vertical'  =>  'center',
                                      'set_wrap'  =>  true ]);
    $header = array_merge($this->status->getHeader('product')['export']
                        , $this->status->getHeader('supplyPrice')['export']
                        , $this->status->getHeader('productSpq')['export']);

    if ( $data['prd-include'] == true ) {
      // $header2 = array_merge(['header' => 'id', 'opts' => ['width' => 8]], $header);
      array_unshift($header, ['header' => 'id', 'opts' => ['width' => 8]]);
      $products = $this->products
                    ->select("product.id, product.barcode, product.productCode, product.img_url")
                    // ->select("CONCAT('[', UPPER(brand.brand_name), '] ', product.name) AS name")
                    // ->select("CONCAT('[', UPPER(brand.brand_name), '] ', product.name_en) AS name_en")
                    ->select("UPPER(brand.brand_name) AS brand_name")
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
                    // ->get()
                    // ->getResultArray();
    }

    // print_r($products);
    $fileName = NULL;
    if ( !empty($products) ) {
      $fileName = $products[0]['brand_name'].'_'.date('Ymd_his');
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
