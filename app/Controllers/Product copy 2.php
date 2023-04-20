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
                                ->select('product_price.supply_rate_applied
                                        , product_price.supply_rate')
                                ->select('supply_price.idx AS supply_price_idx')
                                ->select('supply_price.price')
                                ->select('IFNULL(brand_opts.supply_rate_based, 0) AS supply_rate_based')
                                ->select('IFNULL(brand_opts.supply_rate_by_brand, 0) AS supply_rate_by_brand')
                                ->join('brand', 'brand.brand_id = product.brand_id')
                                ->join('brand_opts', 'brand_opts.brand_id = brand.brand_id', 'left outer')
                                ->join('product_price', 'product_price.product_idx = product.id')
                                ->join('supply_price', 'supply_price.product_idx = product.id', 'left outer')
                                ->where($where)
                                ->where('product_price.available', 1)
                                ->orderBy('brand.own_brand DESC')
                                ->paginate($pageCnt, 'default');
    // echo $this->products->getLastQuery();
    $this->data['pager'] = $this->products->pager;
    return $this->menuLayout('product/main', $this->data);
  }

  public function regist() {
    $controllType = $this->request->uri->getSegment(2);
    $productId = $this->request->uri->getSegment(3);
    $brandId = $this->request->uri->getSegment(4);
    
    // $this->data['brands']  = $this->brands->brands()->findAll();
    
    if ($controllType == 'edit') {
      $this->data['edit'] = true;
      $this->data['product'] = $this->brands->brands()
                                  ->select('product.*')
                                  ->join('product', 'brand.brand_id = product.brand_id')
                                  ->where('product.id', $productId)
                                  ->first();
      $this->data['supply'] = $this->productPrice
                                  ->select('product_price.*')
                                  ->select('supply_price.price, supply_price.not_calculating_margin')
                                  ->join('supply_price', 'supply_price.product_price_idx = product_price.idx', 'left outer')
                                  ->where(['product_price.product_idx'=> $productId, 'product_price.available'=> 1])
                                  ->first();
     $this->data['margin'] = $this->margin
                                  // ->join('margin_rate', 'margin_rate.margin_idx = margin.idx', 'left outer')
                                  // ->where('margin_rate.available', 1)
                                  // ->where('margin_rate.brand_id', $brandId)
                                  ->findAll();                                  
    } else $this->data['brands']  = $this->brands->brands()->findAll();
    return $this->menuLayout('product/register', $this->data);
  }

  public function singleRegist() {
    if ( !empty($this->request->getPost()) ) {
      $data = $this->request->getPost();
    } 
    print_r($data);

    if ( empty($data['product']['brand_id']) ) {      
      return redirect()->back()->withInput()->with('error', '브랜드 선택 안함');
    }

    $brand = $this->brands->where('brand_id', $data['product']['brand_id'])->first();
    if ( empty($brand) ) {
      return redirect()->back()->withInput()->with('error', '해당 브랜드가 없음');
    }

    if ( !empty($data['product']) ) {
      if ( !empty($data['product']['idx']) ) {
        $prdValidCheck = $this->products
                              // ->like('REPLACE(name_en, \' \', \'\')', preg_replace('/\s+/', '', $data['product']['name_en']), 'both')
                              ->like('UPPER(REPLACE(name_en, \' \', \'\'))', preg_replace('/\s+/', '', strtoupper($data['product']['name_en'])), 'both')
                              ->orWhere(['barcode'=> $data['product']['barcode']])
                              ->where(['productCode' => $data['product']['productCode']])
                              ->first();
        
        if ( empty($prdValidCheck) ) {
          if ( $this->products->insert($data['product']) )  {
            $prdIdx = $this->products->getInsertID();
            $data['product_price']['idx'] = $prdIdx;
            
            $validCheck = $this->productPrice->where(['product_idx'=> $prdIdx, 'available' => 1])->first();
            
            if ( !empty($validCheck) ) {
              $this->productPrice->where('idx', $validCheck['idx'])->set('available', 0)->update();
            }
            $this->productPrice->insert($data['product_price']);

            return redirect()->back()->with('error', '등록 성공');
          }
        } else {
          return redirect()->back()->withInput()->with('error', '바코드나 제품명이 이미 사용중에 있습니다.');
        }
      } else {
        // echo "hsa product idx";
        $prdValidCheck = $this->products->where('id', $data['product']['id'])->first();

        if ( !empty($prdValidCheck) ) {
          // if ( !$this->products->save($data['product']) ) {
          //   return redirect()->back()->withInput()->with('error', '제품 등록중에 오류가 발생했습니다.');
          // }
          $data['product_price']['product_idx'] = $prdValidCheck['id'];
          $this->supplyRateEdit($data['product_price']);
        }
        
      }
    } else {
      return redirect()->back()->withInput()->with('error', '데이터 전송 중 오류 발생');
    }

    // print_r($data);
  }

  public function supplyRateEdit($_data = array()) {
    $multiple = false;
    if ( !empty($this->request->getPost()) && empty($_data) ) {
      $data = $this->request->getPost();
      // if ( empty($data['product_price']) ) {
      //   return redirect()->back()->withInput()->with('error', '수정할 상품이 선택되지 않았습니다.');
      // } else $data = $data['product_price'];
    } else $data = $_data;
    echo "<br/><br/>";
    print_r($data);
    echo "<br/><br/>";
    if ( !empty($data) ) {
      $productPrices = $data;
      echo count($data);

      print_r($productPrices);

      // if ( !empty( $productPrices ) ) {
      //   foreach( $productPrices as $productPrice ) {
      //     // print_r($productPrice);
      //     if ( !isset($productPrice['idx']) ) {
      //       echo "idx 없음";
      //       return redirect()->back()->withInput()->with('error', '수정할 상품이 선택되지 않았습니다.');      
      //     } else {
      //       if ( !isset($productPrice['supply_rate_applied']) ) {
      //         $productPrice['supply_rate_applied'] = 0;
      //         $productPrice['supply_rate'] = NULL;
      //       }

      //       $brandOpt = $this->brandOpt->where('brand_id', $productPrice['brand_id'])->first();

      //       if ( !empty($brandOpt) ) {
      //         $price = $this->productPrice
      //                           ->where(['product_idx'=> $productPrice['product_price_idx']
      //                                   , 'available' => 1])
      //                           ->first();

      //         if ( !empty($productPrice) ) {
      //           $supply_rate = round(($productPrice['supply_rate'] / 100), 2);
      //           $retail_price = $price['retail_price'];
                
      //           $condition1 = ['idx' => $price['idx']
      //                         , 'supply_rate_applied' => $productPrice['supply_rate_applied']
      //                         , 'supply_rate' => $supply_rate];
      //           if ($this->productPrice->save($condition1) ) {
      //             $supplyPrice = $this->supplyPrice
      //                                 ->where(['product_idx' => $productPrice['idx']
      //                                         , 'product_price_idx' => $productPrice['product_price_idx']
      //                                         , 'available' => 1])
      //                                 ->first();
                  
      //             if ( !empty($supplyPrice) ) {
      //               $this->supplyPrice
      //                     ->where('idx', $supplyPrice['idx'])
      //                     ->set('price', ($retail_price * $supply_rate))
      //                     ->update();
      //             }
      //           }
      //         }
      //       }
      //     }
      //   }
      // }

      // if ( empty($productPrice['idx']) ) {
      // //   return redirect()->back()->withInput()->with('error', '수정할 상품이 선택되지 않았습니다.');
      // } else {
      //   $brandOpt = $this->brandOpt->where('brand_id', $productPrice['brand_id'])->first();
        
      // //   if ( empty($brandOpt) ) {
      // //     return redirect()->back()->withInput()->with('error', '브랜드별 공급률을 먼저 적용해주세요.');
      // //   } else {
      // //     $supply_rate = round(($productPrice['supply_rate'] / 100), 2);
      // //     $this->productPrice
      // //         ->where('product_idx', $productPrice['idx'])
      // //         ->set(['supply_rate_applied' => 1
      // //               , 'supply_rate' => $supply_rate])
      // //         ->update();
      // //   }
      // }
    }
  }

  public function attachProduct() {
    $validationRule = [
      'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv],'
    ];

    if ( !$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    if ( empty($this->request->getVar('brand_id')) ) {
      return redirect()->back()->withInput()->with('errors', '브랜드 선택 안함');
    }
    // echo 'brand_id'.$this->request->getVar('brand_id')."<Br/>";
    // print_r($this->request->getVar());
    
    if ( $file = $this->request->getFile('file') ) {
      if ( $file->isValid() && !$file->hasMoved() ) {
        $newName = $file->getRandomName();
        $file->move('../public/csvfile', $newName);
        $file = fopen('../public/csvfile/'.$newName, 'r');
        $i = 0;
        $productArr = array();
        // $priceArr = array();
        $numberOfFields = 26;
        
        while( ($filedata = fgetcsv($file, 1000, ",")) !== FALSE ) {
          $num = count($filedata);
          $num = 26;
          // echo $num."<br/>";
          print_r($filedata);
          if ( $i > 0 && $num == $numberOfFields ) {
            $productArr[$i]['brand_id'] = $this->request->getVar('brand_id');
            $productArr[$i]['barcode'] = trim($filedata[0]);
            $productArr[$i]['productCode'] = trim($filedata[1]);
            $productArr[$i]['img_url'] = trim($filedata[2]);
            $productArr[$i]['name'] = encording_check(trim($filedata[3]));
            // $productArr[$i]['name_en'] = trim(ucwords(strtolower($filedata[4])));
            $productArr[$i]['name_en'] = trim($filedata[4]);
            $productArr[$i]['box'] = trim($filedata[5]);
            $productArr[$i]['in_the_box'] = trim($filedata[6]);
            $productArr[$i]['contents_of_box'] = trim($filedata[7]);
            $productArr[$i]['spec'] = trim($filedata[8]);
            $productArr[$i]['spec2'] = trim($filedata[9]);
            $productArr[$i]['container'] = trim($filedata[10]);
            $productArr[$i]['spec_detail'] = trim($filedata[11]);
            $productArr[$i]['spec_pcs'] = trim($filedata[12]);
            $productArr[$i]['shipping_weight'] = trim($filedata[13]);
            $productArr[$i]['sample'] = trim($filedata[14]);
            $productArr[$i]['type'] = encording_check(trim($filedata[15]));
            // $productArr[$i]['type_en'] = trim(ucwords(strtolower($filedata[15])));
            $productArr[$i]['type_en'] = trim($filedata[16]);
            $productArr[$i]['package'] = trim($filedata[17]); // set
            $productArr[$i]['package_detail'] = trim($filedata[18]);  // set 상세
            $productArr[$i]['renewal'] = trim($filedata[19]);
            $productArr[$i]['etc'] = trim($filedata[20]);
            $productArr[$i]['discontinued'] = trim($filedata[21]);
            $productArr[$i]['display'] = trim($filedata[22]);

            $productArr[$i]['retail_price'] = trim($filedata[23]);
            $productArr[$i]['price'] = trim($filedata[24]);
            $productArr[$i]['supply_rate_applied'] = trim($filedata[25]);
            $productArr[$i]['supply_rate'] = trim($filedata[26]);
            $productArr[$i]['taxation'] = trim($filedata[27]);
          }
          $i++;
        }
        fclose($file);

        $count = 0; $updateCnt = 0;
        foreach($productArr as $userdata) {
          $findProduct = $this->products
                            ->where('barcode', $userdata['barcode'])
                            ->where('productCode', $userdata['productCode'])
                            ->where(['name' => $userdata['name']])
                            ->where(['name_en' => $userdata['name_en']])
                            ->where('spec', $userdata['spec'])
                            ->where('type', $userdata['type'])
                            ->first();
          if ( empty($findProduct) ) {
            if ( $this->products->insert($userdata)) { 
              $productId = $this->products->getInsertID();
            
              if ( $productId ) {
                $userdata['product_idx'] = $productId;
                // $userdata['available'] = 1;
                // $this->productSpq->insert();
              }
              $count++;
            }
          // } else { // update0
          //   $productId = $findProduct['id'];
          //   $userdata['brand_id'] = $findProduct['brand_id'];
          //   $userdata['product_idx'] = $productId;
          //   // $userdata['available'] = 1;
          //   $this->products->where(['id' => $findProduct['id']])->set($userdata)->update();

          //   $updateCnt++;
          }

          $prdPrice = $this->productPrice->where(['product_idx' => $productId, 'available' => 1])->first();
          if ( empty($prdPrice) ) {
            $userdata['available'] = 1;
            $this->productPrice->insert($userdata);
          } else {
            $this->productPrice->where(['idx' => $prdPrice['idx']])->set(['available' => 0])->update();

            if ( $this->productPrice->affectedRows() ) {
              $userdata['available'] = 1;
              $this->productPrice->insert($userdata);
            }
          }
        }

        $msg = '';
        if ( $count > 0 ) {
          $msg = $count.' rows successfully added ';
        } 

        if ( $updateCnt > 0 ) {
          $msg.= '<br/>'.$updateCnt.' rows updated';
        }
        // echo $this->products->getLastQuery();
        session()->setFlashdata('message', $msg);
        session()->setFlashdata('alert-class', 'alert-success');
      } else {
        session()->setFlashdata('message', 'CSV file could not be imported');
        session()->setFlashdata('alert-class', 'alert-danger');
      }
    } 

    return redirect()->back()->withInput();
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

    // print_r($data);

    $header = array('Barcode', 'Product Code', 'Product Thumbnail 파일경로'
        , 'Product Name (KOR)', 'Product Name (ENG)'
        , 'Box(묶음) 구성', '박스 내 상품개수', '박스내 구성품 (다른 상품 묶음)'
        , 'Spec', 'spec2', 'container 1: 용기상품 예)아이패치', '상품 스펙 상세(1.5g)'
        , 'Spec Piece(60개)', 'Weight(g)', 'Sample'
        , 'Type', 'Type (ENG)'
        , 'Set', 'Set Detail'
        , 'Renewal', '기타'
        , '상품판매여부 1:판매안함'
        , 'Display 1:표시함'
        , '소비자가'
        , '공급가 (수동공급가 ,구분해서 입력)'
        , '공급률 사용여부 1:공급률사용'
        , '공급률'
        , '공급가 수동변경 1:수동변경'
        , '영세 1:영세');

    if ( $data['prd-include'] == true ) {
      $header = array_merge(['id'], $header);
      $products = $this->products->productJoin()
                    ->select("product.id, product.barcode, product.productCode, product.img_url")
                    // ->select("product.name")
                    // ->select("product.name_en")
                    ->select("CONCAT('[', UPPER(brand.brand_name), '] ', product.name) AS name")
                    ->select("CONCAT('[', UPPER(brand.brand_name), '] ', product.name_en) AS name_en")
                    ->select('product.box, product.in_the_box, product.contents_of_box')
                    ->select("product.spec, product.spec2, product.container, product.spec_detail, product.spec_pcs")
                    ->select("product.shipping_weight, product.sample")
                    ->select("product.type, product.type_en, product.package, product.package_detail")
                    ->select("product.renewal, product.etc")
                    ->select("product.discontinued, product.display")
                    ->select("product_price.retail_price")
                    ->select("IFNULL ( supply_price.price, 0 ) AS price")
                    ->select("IFNULL ( product_price.supply_rate_applied, 0 ) AS supply_rate_applied")
                    ->select("IFNULL ( product_price.supply_rate, '0.00' ) AS supply_rate")
                    ->select("IFNULL ( supply_price.not_calculating_margin, 0 ) AS not_calculating_margin")
                    ->select("product_price.taxation")
                    ->where(['product.discontinued' => 0, 'product.display' => 1])
                    ->where('product_price.available', 1)
                    ->orderBy('brand.brand_id ASC, brand.own_brand DESC, product.id ASC')
                    ->findAll();
                    // ->get()
                    // ->getResultArray();
    }

    // print_r($products);
    $this->dataFile->exportData($header, $products);
  }

  public function attachProductSupplyPrice() {
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
        $numberOfFields = 9;
        
        while( ($filedata = fgetcsv($file, 1000, ",")) !== FALSE ) {
          $num = count($filedata);
          echo $num."<br/>";
          print_r($filedata);
          if ( $i > 0 && $num == $numberOfFields ) {
            $csvArr[$i]['product_idx'] = trim($filedata[0]);
            $csvArr[$i]['retail_price'] = trim($filedata[6]);
            $csvArr[$i]['price'] = trim($filedata[7]);
            $csvArr[$i]['taxation'] = trim($filedata[8]);
            // $csvArr[$i]['available'] = trim();
          }
          $i++;
        }
        fclose($file);

        print_r($csvArr);

        $count = 0;
        foreach($csvArr as $userdata) {
          $findProduct = $this->productPrice
                            ->where('product_idx', $userdata['product_idx'])
                            ->where('available', 1)
                            ->countAllResults();
              
          if ( $findProduct == 0 ) {
            $userdata['available'] = 1;
            if ( $this->productPrice->insert($userdata)) { 
              $productId = $this->productPrice->getInsertID();
              $count++;
            }
          }
        }
        // echo $this->productPrice->getLastQuery();
        session()->setFlashdata('message', $count.' rows successfully added');
        session()->setFlashdata('alert-class', 'alert-success');
      } else {
        session()->setFlashdata('message', 'CSV file could not be imported');
        session()->setFlashdata('alert-class', 'alert-danger');
      }
    } 

    return redirect()->back()->withInput();
  }

  public function attachProductSpq() {
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
