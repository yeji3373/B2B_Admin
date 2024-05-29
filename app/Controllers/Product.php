<?php

namespace App\Controllers;

use App\Models\BrandModel;
use App\Models\BrandOptModel;
use App\Models\MarginModel;
use App\Models\ProductGroupModel;
use App\Models\ProductModel;
use App\Models\ProductPriceModel;
use App\Models\ProductSpqModel;
use App\Models\StockModel;
use App\Models\SupplyPriceModel;
use DataFile\Config\DataFile;
use DataFile\Controllers\DataFileController;
use Status\Config\Status;

class Product extends BaseController
{
  public static $productArr = array();
  public static $productPriceArr = array();
  public static $productMoqArr = array();

  public function __construct() {
    helper(['data', 'select']);
    $this->status = config('Status');
    $this->products = new ProductModel();
    $this->brands = new BrandModel();
    $this->brandOpt = new BrandOptModel();
    $this->productSpq = new ProductSpqModel();
    $this->stocks = new StockModel();
    $this->productPrice = new ProductPriceModel();
    $this->supplyPrice = new SupplyPriceModel();
    $this->margin = new MarginModel();
    $this->pgroup = new ProductGroupModel();

    $this->dataFile = new DataFileController();

    $this->data['header'] = ['css' => ['/table.css', '/product/product.css']
                            , 'js' => ['/product/product.js']];
    $this->data['status'] = $this->status;
  }

  public function index() {
    helper('querystring');
    $data = $this->request->getVar();
    $pageCnt = 10;

    if (!empty($data['pageCnt'])) $pageCnt = $data['pageCnt'];
    $where = !empty(product_query_return($data)) ? join(' AND ', product_query_return($data)) : [];

    $this->data['brands'] = $this->brands->brands()->where('brand.available', 1)->orderBy('brand.brand_name ASC')->find();

    $this->data['products'] = $this->products
        ->select('product.*, brand.brand_name')
        ->select('product_price.idx AS product_price_idx')
        ->select('product_price.retail_price')
        ->select('product_price.supply_price')
        ->select('product_price.supply_rate_applied
                                    , product_price.supply_rate')
        ->select('IFNULL(brand_opts.supply_rate_based, 0) AS supply_rate_based')
        ->select('IFNULL(brand_opts.supply_rate_by_brand, 0) AS supply_rate_by_brand')
        ->join('brand', 'brand.brand_id = product.brand_id', 'straight')
        ->join('brand_opts', 'brand_opts.brand_id = brand.brand_id', 'left outer')
        ->join('product_price', 'product_price.product_idx = product.id', 'straight')
        ->where($where)
        ->where('product_price.available', 1)
        ->orderBy('brand.own_brand DESC')
        ->paginate($pageCnt);

    $this->data['pager'] = $this->products->pager;
    return $this->menuLayout('product/main', $this->data);
  }

  public function regist()
  {
    $controllType = $this->request->uri->getSegment(2);
    // session()->setFlashdata('previous', $this->reqeust->getGet());
    session()->setFlashdata('previous', site_url(previous_url()));

    if ($controllType == 'edit') {
      $productId = $this->request->uri->getSegment(3);
      $brandId = $this->request->uri->getSegment(4);

      $this->data['edit'] = true;

      $this->data['product'] = $this->brands->brands()
          ->select('product.*')
          ->join('product', 'brand.brand_id = product.brand_id', 'STRAIGHT')
          ->where('product.id', $productId)
          ->first();

      $this->data['supply'] = $this->productPrice
          ->where(['product_price.product_idx' => $productId
                , 'product_price.available' => 1])
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
          ->join('margin_rate', 'margin_rate.margin_idx = margin.idx AND margin_rate.available = 1 AND margin_rate.brand_id = ' . $brandId, 'left outer')
          ->join('supply_price', "supply_price.margin_idx = margin.idx AND supply_price.product_idx=${productId}", 'left outer')
          ->findAll();

      // var_dump($this->data['margin']);
      $this->data['pgroups'] = $this->pgroup->where(['brand_id' => $brandId])->findAll();
    } else {
      $this->data['brands'] = $this->brands->brands()->orderby('brand_name ASC')->findAll();
      $this->data['margin'] = $this->margin->findAll();
    }

    return $this->menuLayout('product/register', $this->data);
  }

  public function singleRegist()
  {
    $data = array();
    if (!empty($this->request->getPost())) {
      $data = $this->request->getPost();
    }

    if (empty($data['product']['brand_id'])) {
      return redirect()->back()->withInput()->with('error', '브랜드 선택 안함');
    }

    $brand = $this->brands->where('brand_id', $data['product']['brand_id'])->first();
    if (empty($brand)) {
      return redirect()->back()->withInput()->with('error', '해당 브랜드가 없음');
    }

    //그룹핑
    if (!empty($data['grouping']) && !empty($data['group']['id'])) {
      if ($data['group']['id'] == 'new_group') {
        //그룹 신규등록 후 추가
        $groupValidCheck = $this->pgroup
            ->like('UPPER(REPLACE(group_name, \' \', \'\'))', preg_replace('/\s+/', '', strtoupper($data['group']['name_new'])), 'both')
            ->first();
        if (!empty($groupValidCheck)) {
          print_r($groupValidCheck);
          echo "이미 같은 이름의 그룹이 있음";
          return redirect()->back()->withInput()->with('error', '같은 이름의 그룹이 있습니다.');
        } else {
          if (!empty($data['product']['id'])) {
            if ($this
                  ->pgroup
                  ->save(['brand_id' => $data['product']['brand_id']
                          ,'group_name' => $data['group']['name_new']])) {
              $group_idx = $this->pgroup->getInsertID();
              $this
                ->products
                ->save(['group_id' => $group_idx
                      , 'id' => $data['product']['id']
                      , 'brand_id' => $data['product']['brand_id']]);
            }
          }
        }
      } else {
        //기존그룹에 추가
        if (!empty($data['product']['id'])) {
            $this
              ->products
              ->save(['group_id' => $data['group']['id']
                      , 'id' => $data['product']['id']
                      , 'brand_id' => $data['product']['brand_id']]);
        }
      }
    }

    if (!empty($data['product'])) {
      if (empty($data['product']['id'])) {
        $prdValidCheck = $this->products
            ->like('UPPER(REPLACE(name_en, \' \', \'\'))', preg_replace('/\s+/', '', strtoupper($data['product']['name_en'])), 'both')
            ->orWhere(['barcode' => $data['product']['barcode']])
            ->where(['productCode' => $data['product']['productCode']])
            ->first();

        if (empty($prdValidCheck)) {
          if ($this->products->insert($data['product'])) {
            $prdIdx = $this->products->getInsertID();
            $data['product_price']['product_idx'] = $prdIdx;

            $validCheck = $this->productPrice->where(['product_idx' => $prdIdx, 'available' => 1])->first();

            if (!empty($validCheck)) {
                $this->productPrice->where('idx', $validCheck['idx'])->set('available', 0)->update();
            } else {
              if ($this->productPrice->insert($data['product_price'])) {
                $pPriceIdx = $this->productPrice->getInsertID();

                $margins = $this->margin
                          ->join('margin_rate', 'margin_rate.margin_idx = margin.idx')
                          ->where(['margin_rate.available' => 1
                              , 'margin_rate.brand_id' => $data['product']['brand_id']])
                          ->orderBy('margin.idx ASC')
                          ->findAll();
                if (!empty($margins)) {
                    foreach ($margins as $margin) {
                        $this->supplyPrice->save(['product_idx' => $prdIdx
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
        // 제품 등록이 되어서 이미 제품 가격이 있을 때.
        if (!$this->products->save($data['product'])) {
          return redirect()->back()->withInput()->with('error', '제품 등록중에 오류가 발생했습니다.');
        }

        if (!empty($data['product_price'])) {
          $this->supplyRateEdit($data['product_price']);
        }
      }
    } else {
      return redirect()->back()->withInput()->with('error', '데이터 전송 중 오류 발생');
    }

    if (session()->has('previous')) {
      return redirect()->to(session()->getFlashdata('previous'));
    } else {
      return redirect()->back();
    }

  }

  public function supplyRate()
  {
    if (!empty($this->request->getPost())) {
      $data = $this->request->getPost('product_price');
    }

    if (!empty($data)) {
      foreach ($productPrices as $productPrice) {
        $this->supplyRateEdit($productPrice);
      }
      return redirect()->back();
    } else {
      return redirect()->back()->withInput()->with('error', '변경할 정보가 없습니다.');
    }
  }

  public function supplyRateEdit($data = array())
  {
    if (!empty($data)) {
      if (!isset($data['idx'])) { // product check
        return redirect()->back()->withInput()->with('error', '수정할 상품이 선택되지 않았습니다.');
      } else {
        $_temp['idx'] = $data['product_price_idx'];
        $_temp['product_idx'] = $data['idx'];

        unset($data['idx']);
        unset($data['product_price_idx']);

        $data['idx'] = $_temp['idx'];
        $data['product_idx'] = $_temp['product_idx'];

        if (!isset($data['supply_rate_applied']) || empty($data['supply_rate_applied'])) { // 상품별 공급률 변경일 경우
          $data['supply_rate_applied'] = 0;
          $data['supply_rate'] = null;
        }

        if (empty($data['brand_id'])) {
          return redirect()->back()->withInput()->with('error', '브랜드 정보가 없습니다');
        } else {
          $getProductPrice = $this->productPrice->where(['idx' => $data['idx'], 'product_idx' => $data['product_idx'], 'available' => 1])->first();
          $brandOpt = $this->brandOpt->where(['brand_id' => $data['brand_id'], 'available' => 1])->first();
          if (!empty($brandOpt)) {
              // if ( !empty($getProductPrice) ) {
              //   $data['idx'] = $getProductPrice['idx'];

              //   if ( !empty($data['supply_rate_applied']) ) {
              //     $data['supply_rate'] = round(($data['supply_rate'] / 100), 2);
              //   } else {
              //     $data['supply_rate'] = $brandOpt['supply_rate_by_brand'];
              //   }

              //   if ( $getProductPrice['retail_price'] == $data['retail_price'] ) unset($data['retail_price']);
              //   // 기존 available = 0으로 변경하고 새로 입력하기. 꼭!!!!
              //   // $priceCondition = ['idx' => $data['idx']
              //   //                     , 'retail_price' => $data['retail_price']
              //   //                     , 'supply_rate_applied' => $data['supply_rate_applied']
              //   //                     , 'supply_rate' => $data['supply_rate']
              //   //                     , 'supply_price' => ($getProductPrice['retail_price'] * $data['supply_rate'])];
              //   if ( !$this->productPrice->save($priceCondition) ) {
              //     // $supplyPrice = $this->supplyPrice
              //     //                     ->where(['product_idx' => $data['idx']
              //     //                             , 'product_price_idx' => $data['product_price_idx']
              //     //                             , 'available' => 1])
              //     //                     ->findAll();

              //     // if ( !empty($supplyPrice) ) {
              //     //   foreach($supplyPrice AS $sPrice) {
              //     //     $this->supplyPrice->save(['idx'=> $supplyPrice['idx'], 'price', ($price['retail_price'] * $supply_rate)]);
              //     //   }
              //     // }
              //   }
              // }
          } else {
            if (!empty($getProductPrice)) {
              if (isset($data['not_calculating_margin']) && !empty($data['not_calculating_margin'])) {
              } else {
                $data['not_calculating_margin'] = 0;
              }

              if (empty($data['not_calculating_margin'])) {
                if (empty($data['supply_price'])) {
                  session()->setFlashdata('error', '공급가가 없습니다.');
                  return redirect()->back()->withInput();
                }
              }

              if (!empty($data['price'])) {
                $prices = $data['price'];
                unset($data['price']);
                if (strtolower(gettype($prices)) == 'array') {
                  foreach ($prices as $price) {
                    $price['product_price_idx'] = $data['idx'];
                    $price['product_idx'] = $data['product_idx'];

                    if (isset($price['supply_price_idx'])) {
                      $price['idx'] = $price['supply_price_idx'];
                      if (empty($data['not_calculating_margin']) && !empty($data['supply_price']) && !empty($price['margin_rate'])) {
                        $price['price'] = round($data['supply_price'] * $price['margin_rate']);
                      }
                      var_dump($price);
                      if (!empty($price['price']) && !empty($price['idx'])) {
                        $this->supplyPrice->save($price);
                      }
                    } else {

                    }
                  }
                }
              }

              if (!$this->productPrice->save($data)) {
                session()->setFlashdata('error', '상품가격 등록중 오류 발생');
                return redirect()->back()->withInput();
              }
            }
          }
        }
      }
    }
  }

  public function attachProduct() {
    $validationRule = [
      // 'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv],'
      'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv,xls,xlsx],',
    ];
    $brand_id = null;
    $failedData = [];
    $options = $this->request->getPost();
    $header = [];

    if (!$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    if (empty($this->request->getVar('brand_id'))) {
      return redirect()->back()->withInput()->with('error', '브랜드 선택 안함');
    } else {
      $brand_id = $this->request->getvar('brand_id');
    }

    if (empty($options['prd-include'])
        && empty($options['prd-price-include'])
        && empty($options['prd-moq-include'])) {
      return redirect()->back()->withInput()->with('error', '옵션 선택 안함');
    }

    if ($this->request->getFile('file')) {
      $file = $this->request->getFile('file');
      $data = $this->dataFile->attachData($file);

      if (!empty($data)) {
        $numberOfRecords = $this->dataFile->numberOfRecords;
        $numberOfFields = $this->dataFile->numberOfFields;

        $excelFields = $data[0];
        $product_header = array();
        $product_price_header = array();
        $product_moq_header = array();

        if (!empty($data['prd-include'])
            && !empty($data['prd-price-include'])
            && !empty($data['prd-moq-include'])) {
          $header = array_merge($this->status->getHeader()['export']
                            , $this->status->getHeader('product')['export']
                          , $this->status->getHeader('supplyPrice')['export']
                          , $this->status->getHeader('productSpq')['export']);
        } else {
          if (!empty($options['prd-include'])) {
            if (empty($header)) $header = $this->status->getHeader()['export'];
            
            $product_header = array_merge($this->status->getHeader()['export'], $this->status->getHeader('product')['export']);
            $header = array_merge($header, $this->status->getHeader('product')['export']);
          }

          if (!empty($options['prd-price-include'])) {
            if (empty($header)) $header = $this->status->getHeader('default')['export'];

            $product_price_header = array_merge($this->status->getHeader('idOnly')['export'], $this->status->getHeader('supplyPrice')['export']);
            $header = array_merge($header, $this->status->getHeader('supplyPrice')['export']);
          }

          if (!empty($options['prd-moq-include'])) {
            if (empty($header)) $header = $this->status->getHeader('default')['export'];

            $product_moq_header = array_merge($this->status->getHeader('idOnly')['export'], $this->status->getHeader('productSpq')['export']);
            $header = array_merge($header, $this->status->getHeader('productSpq')['export']);
          }

          if (count($excelFields) != count($header)) {
              return redirect()->back()->withInput()->with('error', 'update 내용/형식이 다릅니다');
          } else {
            foreach ($excelFields as $i => $key):
              // echo preg_replace('/[^A-Za-z0-9-가-하]/', '', $key).' '.str_replace($headerValidRules, '', $header[$i]['header']).'<br/>';
             if (preg_replace('/\s+/', '', $key) != preg_replace('/\s+/', '', $header[$i]['headerValid'])) {
                return redirect()->back()->withInput()->with('error', '형식이 다릅니다.');
              }
            endforeach;
          }
        }

        $productArr = array();
        $productPriceArr = array();
        $productMoqArr = array();

        $contents = array_splice($data, 1, $numberOfFields);

        if (!empty($options['prd-include'])) {
          foreach ($contents as $i => $fileRowsData) {
            $k = 0;
            foreach ($product_header as $j => $prd_header) {
              if (preg_replace('/\s+/', '', $prd_header['headerValid']) == preg_replace('/\s+/', '', $excelFields[$j])) {
                if ($prd_header['field'] == 'brand_id') {
                  if (empty($fileRowsData[$j])) {
                    if (!empty($options['brand_id'])) {
                      $fileRowsData[$j] = $options['brand_id'];
                    }
                  }
                }

                $productArr[$i][$prd_header['field']] = $fileRowsData[$j];
                if ($prd_header['field'] != 'id' ) {
                  array_splice($contents[$i], $k, 1);
                  $k = 1;
                }
              }
            }
            $contents[$i][0] = self::setProductInfo($productArr[$i]);
          }
          array_splice($excelFields, 1, (count($product_header) - 1));
        } else {
          array_splice($excelFields, 1, count($this->status->getHeader('default')['export']) - 1);
        }

        if (!empty($options['prd-price-include'])) {
          foreach ($contents as $i => $fileRowsData) {
            if ( empty($options['prd-include']) ) {
              array_splice($fileRowsData, 1, count($this->status->getHeader('default')['export']) - 1);
            }
            $k = 0;
            foreach ($product_price_header as $j => $price_header) {
              if (preg_replace('/\s+/', '', $price_header['headerValid']) == preg_replace('/\s+/', '', $excelFields[$j])) {
                $productPriceArr[$i][$price_header['field']] = $fileRowsData[$j];
                if ($price_header['field'] != 'id') {
                  array_splice($contents[$i], $k, 1);
                  $k = 1;
                }
              }
            }
            $productPriceArr[$i]['brand_id'] = $options['brand_id'];
            $contents[$i][0] = $fileRowsData[0];
            self::setProductPriceInfo($productPriceArr[$i]);
            // $contents[$i]['idx'] = self::setProductPriceInfo($productPriceArr[$i]);
          }
          array_splice($excelFields, 1, (count($product_price_header) - 1));
        }

        if (!empty($options['prd-moq-include'])) {
          foreach ($contents as $i => $fileRowsData) {
            if ( empty($options['prd-include']) ) {
              array_splice($fileRowsData, 1, count($this->status->getHeader('default')['export']) - 1);
            }
            $k = 0;
            foreach ($product_moq_header as $j => $moq_header) {
              if (preg_replace('/\s+/', '', $moq_header['headerValid']) == preg_replace('/\s+/', '', $excelFields[$j])) {
                $productMoqArr[$i][$moq_header['field']] = $fileRowsData[$j];
                array_splice($contents[$i], $k, 1);
              }
            }
            self::setProductMoqInfo($productMoqArr[$i]);
          }
        }
      }
      return redirect()->back()->with('error', '완료');
    }
  }

  public function exportData() {
    $brandId = (int) $this->request->uri->getSegment(3);
    $products = [];
    $brandInfo;
    $fileName = null;
    $header = [];

    if (!empty($this->request->getPost())) {
      $data = $this->request->getPost();

      // var_dump($data);
      // return;
      if (empty($brandId)) {
        if (!empty($data['brand_id'])) {
          $brandId = $data['brand_id'];
        }
      }
      // 미표기, 판매안하는 상품 포함 및 제외 처리하기
    }

    if (!empty($brandId)) {
      $brandInfo = $this->brands->where('brand_id', $brandId)->first();
      $this->products->where('product.brand_id', $brandId);

      $fileName = $brandInfo['brand_name'] . "_" . date('Ymd_his');
    } else {
      $fileName = 'BeautynetKorea_' . date('Ymd_his');
    }

    $this->dataFile
          ->exportOptions(['width' => 15],
                          ['bold' => true,
                            'fill' => ['color' => 'FFF5DEB3'],
                            'align_vertical' => 'center',
                            'set_wrap' => true,
                            'colCnt' => 2,
                            'colName' => ['header', 'field']]);

    if (!empty($data['prd-include'])
        && !empty($data['prd-price-include'])
        && !empty($data['prd-moq-include'])) {
      $header = array_merge($this->status->getHeader()['export']
                            , $this->status->getHeader('product')['export']
                            , $this->status->getHeader('supplyPrice')['export']
                            , $this->status->getHeader('productSpq')['export']);
      $products = $this->products->productDefault()->productPriceJoin()->productMoqJoin();
    } else {
      if (empty($data['prd-include'])
          && empty($data['prd-price-include'])
          && empty($data['prd-moq-include'])) {
        $header = array_merge($this->status->getHeader()['export']
                            , $this->status->getHeader('product')['export']
                            , $this->status->getHeader('supplyPrice')['export']
                            , $this->status->getHeader('productSpq')['export']);
        $products = $this->brands
                      ->select("'' AS id, brand_id, brand_name")
                      ->where('brand_id', $brandId)
                      ->findAll();

        $this->dataFile->exportData($header, $products, $fileName, 'xls');
      } else {
        if (!empty($data['prd-include'])) {
          if (empty($header)) $header = $this->status->getHeader()['export'];

          $header = array_merge($header, $this->status->getHeader('product')['export']);
          $products = $this->products->productDefault();
        }

        if (!empty($data['prd-price-include'])) {
          if (empty($header)) {
            $header = $this->status->getHeader('default')['export'];
            $product = $this->products->productMinimalizeJoin();
          } else {
            $this->products->select('product_price.product_idx AS product_idx')
            ->select('product_price.idx AS product_price_idx');
          }
          $header = array_merge($header, $this->status->getHeader('supplyPrice')['export']);
          $products = $this->products->productPriceJoin();
        }

        if (!empty($data['prd-moq-include'])) {
          if (empty($header)) {
            $header = $this->status->getHeader('default')['export'];
            $product = $this->products->productMinimalizeJoin();
          }
          $header = array_merge($header, $this->status->getHeader('productSpq')['export']);
          $products = $this->products->productMoqJoin();
        }
      }
    }

    $products = $this->products->findAll();
    $this->dataFile->exportData($header, $products, $fileName, 'xls');
  }

  public function setProductInfo($product = array()) {
    if ( is_null($product['brand_id']) ) return;
    if ( is_null($product['brand']) ) return;
    if ( is_null($product['name_en']) ) return;
    if ( !is_null($product['barcode']) ) $product['barcode'] = preg_replace('/\s+/', '', $product['barcode']);
    
    if ( is_null($product['box']) ) $product['box'] = 0;
    if ( is_null($product['in_the_box']) ) $product['in_the_box'] = 0;
    if ( is_null($product['container']) ) $product['container'] = 0;
    if ( is_null($product['discontinued']) ) $product['discontinued'] = 0;
    if ( is_null($product['display']) ) $product['display'] = 0;
    if ( is_null($product['shipping_weight']) ) $product['shipping_weight'] = 0;
    if ( is_null($product['type']) ) $product['type'] = NULL;
    if ( is_null($product['type_en']) ) $product['type_en'] = NULL;
    
    if ( is_null($product['name']) ) $product['name'] = NULL;
    else addslashes($product['name']); // preg_replace('/[^A-Za-z0-9-가-하.+]/', ' ', $product['name']);

    if ( is_null($product['productCode']) ) $product['productCode'] = NULL;
    if ( is_null($product['img_url']) ) $product['img_url'] = 'img/no-image.png';

    // var_dump($product);
    $getBrands = $this->brands->where(['UPPER(brand_name)' => strtoupper($product['brand'])] )->first();
    if ( !empty($getBrands) ) {
      if ( $getBrands['brand_id'] != $product['brand_id'] ) {
        $product['brand_id'] = $getBrands['brand_id'];
      }
    } else return; // 일치하는 브랜드가 없음.
    // var_dump($product);
    // return;
    
    if ( is_null($product['id']) ) {
      $prd = $this
              ->products
              ->where( 'REPLACE(brand_id, " ", "")', ltrim($product['brand_id']) )
              ->where(  IS_NULL($product['barcode']) ?
                        "(barcode IS NULL OR barcode = '')" :
                        "REPLACE(barcode, ' ', '') ='" . preg_replace("/\s+/", "", $product['barcode']). "'" )
              ->where( 'UPPER(REPLACE(name_en, " ", ""))', addslashes(preg_replace('/\s+/', '', strtoupper($product['name_en']))) )
              ->where( IS_NULL($product['type_en']) ? 
                        'type_en IS NULL' : 
                        "UPPER(REPLACE(type_en, ' ', '')) = '" . addslashes(preg_replace('/\s+/', '', strtoupper($product['type_en']))) ."'")
              ->first();

      echo $this->products->getLastQuery()."<br/>";
      if ( !empty($prd) ) {
        unset($prd['created_at']);
        unset($prd['updated_at']);
        $diff = array_diff($prd, $product);

        if ( !empty($diff) ) {
          if ( array_key_exists('id', $diff) ) {
            if ( count($diff) > 1 ) {
              $this->products->save($diff);
            }
          }
        }
        return $prd['id'];
      } else {
        echo "!empty save<br/>";
        if ( $this->products->save($product) ) {
          return $this->products->getInsertID();
        } 
      }
    } else {
      $prd = $this->where('id', $product['id'])->findAll();
      var_dump($prd);
      if ( !empty($prd) ) {
        $temp = array_diff($product, $prd);
        $temp['id'] = $product['id'];
        if ( $this->products->save($temp) ) {
          return $this->products->getInsertID();
        }
      } else {
        if ( $this->products->save($product) ) {
          return $this->products->getInsertID();
        }
      }
    }
    return null;
  }

  public function setProductPriceInfo($price = array() ) {
    if (is_null($price['id'])) return;
    if (is_null($price['retail_price'])) $price['retail_price'] = 0;
    if (is_null($price['supply_rate_applied'])) $price['supply_rate_applied'] = 0;
    if (is_null($price['supply_rate'])) $price['supply_rate'] = null;
    if (is_null($price['taxation'])) $price['taxation'] = 0;
    
    if ( is_null($price['not_calculating_margin']) ) $price['not_calculating_margin'] = 0; 
  
    $price['product_idx'] = $price['id'];
    unset($price['id']);

    $prdPrice = $this
                  ->productPrice
                  ->where(['product_idx' => $price['product_idx']])
                  ->first();
    
    $price['available'] = 1;

    if ( empty($prdPrice) ) {
      if ( $this->productPrice->save($price) ) {
        unset($price['available']);
        unset($price['retail_price']);
        unset($price['supply_rate_applied']);
        unset($price['taxation']);
        $price['product_price_idx'] = $this->productPrice->getInsertID();
        // self::setProductSupplyPriceInfo($price);
      }
    } else {
      $price['idx'] = $prdPrice['idx'];
      $price['product_idx'] = $prdPrice['product_idx'];

      if ( $this->productPrice->save($price) ) {
        $price['product_price_idx'] = $price['idx'];
        unset($price['idx']);
        unset($price['available']);
        unset($price['retail_price']);
        unset($price['supply_rate_applied']);
        unset($price['taxation']);
      }
    }
    self::setProductSupplyPriceInfo($price);
  }
  
  public function setProductSupplyPriceInfo($supplyPrice = array()) {
    if (is_null($supplyPrice['product_idx'])) return;
    if (is_null($supplyPrice['product_price_idx'])) return;

    $supplies = self::convertSupplyPriceInfo($supplyPrice);

    foreach($supplies AS $supply) {
      $prdSupplyPrice = $this
                          ->supplyPrice
                          ->where(['product_idx' => $supply['product_idx']
                                  , 'product_price_idx' => $supply['product_price_idx']
                                  , 'margin_idx' => $supply['margin_idx']
                                  , 'margin_level' => $supply['margin_level']])
                          ->orderBy('margin_level ASC')
                          ->first();

      if ( !empty($prdSupplyPrice) ) {
        if ( $supply['price'] != $prdSupplyPrice['price'] ) {
          if ( $prdSupplyPrice['available'] ) {
            $this->supplyPrice->save(['idx' => $prdSupplyPrice['idx'], 'available' => 0]);
          }
        }
        $supply['idx'] = $prdSupplyPrice['idx'];
      }

      if ( !array_key_exists('available', $supply) ) {
        $supply['available'] = 1;
      }
      $this->supplyPrice->save($supply);
    }
  }

  public function setProductMoqInfo($spq = array()) {
    if (empty($spq['id']) ) return;

    $spq['product_idx'] = $spq['id'];
    $spq['available'] = 1;
    unset($spq['id']);

    $prdSpq = $this
                ->productSpq
                ->where(['product_idx' => $spq['product_idx']])
                ->first();

    if ( !empty($prdSpq) ) {
      $spq['id'] = $prdSpq['id'];
    }
    $this->productSpq->save($spq);
  }

  public function convertSupplyPriceInfo($supplyPrice = array()) {
    $tmpSupplyPrice = array();
    if ( !empty($supplyPrice['not_calculating_margin']) ) {
      $margins = $this
                  ->margin
                  ->join('margin_rate', 'margin_rate.margin_idx = margin.idx', 'STRAIGHT')
                  ->where('margin_rate.brand_id', $supplyPrice['brand_id'])
                  ->where('margin_rate.available', 1)
                  ->orderBy('margin_level ASC')
                  ->findAll();
      
      if ( !empty($margins) ) {
        $tmpPrice = array();
        if ( !empty($supplyPrice['price']) ) {
          $tmpPrice = explode('/', $supplyPrice['price']);

          if ( count($tmpPrice) == count($margins) ) {
            foreach($margins as $i => $margin) {
              $tmpSupplyPrice[$i]['margin_idx'] = $margin['margin_idx'];
              $tmpSupplyPrice[$i]['margin_level'] = $margin['margin_level'];
              $tmpSupplyPrice[$i]['price'] = $tmpPrice[$i];
              $tmpSupplyPrice[$i]['product_idx'] = $supplyPrice['product_idx'];
              $tmpSupplyPrice[$i]['product_price_idx'] = $supplyPrice['product_price_idx'];
              // $this->supplyPrice->save($supplyPrice);
            }
          }
        }
      }
    } else {
      if ( !empty($supplyPrice['supply_price']) ) {
        $brandMargin = $this
                          ->margin
                          ->select("margin.margin_level AS margin_level")
                          ->select('margin_rate.margin_idx AS margin_idx, margin_rate.margin_rate AS margin_rate')
                          ->join('margin_rate', 'margin_rate.margin_idx = margin.idx')
                          ->where(['margin_rate.brand_id' => $supplyPrice['brand_id']
                              , 'margin_rate.available' => 1])
                          ->orderBy('margin.margin_level ASC')
                          ->findAll();

        if ( !empty($brandMargin) ) {
          foreach($brandMargin AS $i => $margin) {
            $tmpSupplyPrice[$i]['product_idx'] = $supplyPrice['product_idx'];
            $tmpSupplyPrice[$i]['product_price_idx'] = $supplyPrice['product_price_idx'];
            $tmpSupplyPrice[$i]['margin_idx'] = $margin['margin_idx'];
            $tmpSupplyPrice[$i]['margin_level'] = $margin['margin_level'];
            $tmpSupplyPrice[$i]['price'] = ceil(($supplyPrice['supply_price'] * $margin['margin_rate']));
            // $this->supplyPrice->save($supplyPrice);
          }
        }
      }
    }
    return $tmpSupplyPrice;
  }

  public function attachProductSupplyPrice() {
    if ( empty($productSupplyPrice) ) return;
    else {
      foreach($productSupplyPrice AS $supplyPrice ) {

      }
    }
      // $validationRule = [
      //   'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv,xlsx,xls],'
      // ];

      // if ( !$this->validate($validationRule)) {
      //   return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
      // }

      // if ( $this->request->getFile('file') ) {
      //   $file = $this->request->getFile('file');
      //   $data = $this->dataFile->attachData($file);
      //   $priceFields = $this->status->getHeader('supplyPrice')['fields'];

      //   if ( !empty($data) ) {

      //   }
      //   // if ( $file->isValid() && !$file->hasMoved() ) {
      //   //   $newName = $file->getRandomName();
      //   //   $file->move('../public/csvfile', $newName);
      //   //   $file = fopen('../public/csvfile/'.$newName, 'r');
      //   //   $i = 0;
      //   //   $csvArr = array();
      //   //   $numberOfFields = 9;

      //   //   while( ($filedata = fgetcsv($file, 1000, ",")) !== FALSE ) {
      //   //     $num = count($filedata);
      //   //     echo $num."<br/>";
      //   //     print_r($filedata);
      //   //     if ( $i > 0 && $num == $numberOfFields ) {
      //   //       $csvArr[$i]['product_idx'] = trim($filedata[0]);
      //   //       $csvArr[$i]['retail_price'] = trim($filedata[6]);
      //   //       $csvArr[$i]['price'] = trim($filedata[7]);
      //   //       $csvArr[$i]['taxation'] = trim($filedata[8]);
      //   //       // $csvArr[$i]['available'] = trim();
      //   //     }
      //   //     $i++;
      //   //   }
      //   //   fclose($file);

      //   //   print_r($csvArr);

      //   //   $count = 0;
      //   //   foreach($csvArr as $userdata) {
      //   //     $findProduct = $this->productPrice
      //   //                       ->where('product_idx', $userdata['product_idx'])
      //   //                       ->where('available', 1)
      //   //                       ->countAllResults();

      //   //     if ( $findProduct == 0 ) {
      //   //       $userdata['available'] = 1;
      //   //       if ( $this->productPrice->insert($userdata)) {
      //   //         $productId = $this->productPrice->getInsertID();
      //   //         $count++;
      //   //       }
      //   //     }
      //   //   }
      //   //   // echo $this->productPrice->getLastQuery();
      //   //   session()->setFlashdata('message', $count.' rows successfully added');
      //   //   session()->setFlashdata('alert-class', 'alert-success');
      //   // } else {
      //   //   session()->setFlashdata('message', 'CSV file could not be imported');
      //   //   session()->setFlashdata('alert-class', 'alert-danger');
      //   // }
      // } else {
      //   session()->setFlashdata('message', 'CSV file could not be imported');
      //   session()->setFlashdata('alert-class', 'alert-danger');
      // }

      // return redirect()->back()->withInput();
  }

  public function attachProductSpq()
  {
      $validationRule = [
          'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv,xls,xlsx],',
      ];

      if (!$this->validate($validationRule)) {
          return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
      }

      if ($file = $this->request->getFile('file')) {
          if ($file->isValid() && !$file->hasMoved()) {
              $newName = $file->getRandomName();
              $file->move('../public/csvfile', $newName);
              $file = fopen('../public/csvfile/' . $newName, 'r');
              $i = 0;
              $csvArr = array();
              $numberOfFields = 10;

              while (($filedata = fgetcsv($file, 1000, ",")) !== false) {
                  $num = count($filedata);
                  if ($i > 0 && $num == $numberOfFields) {
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
              foreach ($csvArr as $userdata) {
                  // $findProduct = $this->productSpq
                  //                   ->where('product_idx', $userdata['product_idx'])
                  //                   ->where('available', 1)
                  //                   ->countAllResults();

                  // if ( $findProduct == 0 ) {
                  $userdata['available'] = 1;
                  if ($this->productSpq->insert($userdata)) {
                      $productId = $this->productSpq->getInsertID();
                      $count++;
                  }
                  // }
              }
              // echo $this->productSpq->getLastQuery();
              session()->setFlashdata('message', $count . ' rows successfully added');
              session()->setFlashdata('alert-class', 'alert-success');
          } else {
              session()->setFlashdata('message', 'CSV file could not be imported');
              session()->setFlashdata('alert-class', 'alert-danger');
          }
      }

      return redirect()->back()->withInput();
  }

  public function attachStocks()
  { // 수정하기. stocks detail에 입고일에 이미 들어온게 있는지 확인. available = 1 이고 (supplied qty - salse qty - pending qty) > 0 한 것의 합계 넣어주기
      $validationRule = [
          'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv],',
      ];

      if (!$this->validate($validationRule)) {
          return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
      }

      if ($file = $this->request->getFile('file')) {
          if ($file->isValid() && !$file->hasMoved()) {
              $newName = $file->getRandomName();
              $file->move('../public/csvfile', $newName);
              $file = fopen('../public/csvfile/' . $newName, 'r');
              $i = 0;
              $csvArr = array();
              $numberOfFields = 7;

              while (($filedata = fgetcsv($file, 1000, ",")) !== false) {
                  $num = count($filedata);
                  if ($i > 0 && $num == $numberOfFields) {
                      $csvArr[$i]['product_idx'] = trim($filedata[0]);
                      $csvArr[$i]['spq_criteria'] = trim($filedata[6]);
                  }
                  $i++;
              }
              fclose($file);

              print_r($csvArr);

              $count = 0;
              foreach ($csvArr as $userdata) {
                  $findProduct = $this->productSpq
                      ->where('product_idx', $userdata['product_idx'])
                      ->where('available', 1)
                      ->countAllResults();

                  if ($findProduct == 0) {
                      $userdata['available'] = 1;
                      if ($this->productSpq->insert($userdata)) {
                          $productId = $this->productSpq->getInsertID();
                          $count++;
                      }
                  }
              }
              // echo $this->productSpq->getLastQuery();
              session()->setFlashdata('message', $count . ' rows successfully added');
              session()->setFlashdata('alert-class', 'alert-success');
          } else {
              session()->setFlashdata('message', 'CSV file could not be imported');
              session()->setFlashdata('alert-class', 'alert-danger');
          }
      }

      return redirect()->back()->withInput();

  }
}
