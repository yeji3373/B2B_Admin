<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\BrandModel;

class Product extends BaseController {
  public function __construct() {
    $this->products = new ProductModel();
    $this->brands = new BrandModel();
  }

  // public function _remap(...$params) {
  //   $method = $this->request->getMethod();
  //   $params = [($params[0] !== 'index' ? $params[0] : false)];
  //   $this->data = $this->request->getJSON();
  // }

  public function index() {
    return $this->menuLayout('product/main');
  }

  public function regist() {
    $brand = $this->brands->findAll();
    $data['brands'] = $brand;
    
    return $this->menuLayout('product/register', $data);
  }

  public function attachProduct() {
    helper('data');

    $validationRule = [
      'file' => 'uploaded[file]|max_size[file,4096]|ext_in[file,csv],'
    ];

    if ( !$this->validate($validationRule)) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    echo 'brand_id'.$this->request->getVar('brand_id')."<Br/>";
    print_r($this->request->getVar());
    
    if ( $file = $this->request->getFile('file') ) {
      if ( $file->isValid() && !$file->hasMoved() ) {
        $newName = $file->getRandomName();
        $file->move('../public/csvfile', $newName);
        $file = fopen('../public/csvfile/'.$newName, 'r');
        $i = 0;
        $numberOfFields = 19;
        $csvArr = array();
        
        while( ($filedata = fgetcsv($file, 1000, ",")) !== FALSE ) {
          $num = count($filedata);
          print_r($filedata);
          if ( $i > 0 && $num == $numberOfFields ) {
            $csvArr[$i]['brand_id'] = $this->request->getVar('brand_id');
            $csvArr[$i]['barcode'] = trim($filedata[0]);
            $csvArr[$i]['sample'] = trim($filedata[10]);
            $csvArr[$i]['name'] = encording_check(trim($filedata[1]));
            $csvArr[$i]['name_en'] = trim($filedata[2]);
            $csvArr[$i]['box'] = trim($filedata[3]);
            $csvArr[$i]['in_the_box'] = trim($filedata[4]);
            $csvArr[$i]['spec'] = trim($filedata[5]);
            $csvArr[$i]['container'] = trim($filedata[6]);
            $csvArr[$i]['spec_detail'] = trim($filedata[7]);
            $csvArr[$i]['spec_pcs'] = trim($filedata[8]);
            $csvArr[$i]['shipping_weight'] = trim($filedata[9]);
            $csvArr[$i]['type'] = encording_check(trim($filedata[11]));
            $csvArr[$i]['type_en'] = trim($filedata[12]);
            $csvArr[$i]['package'] = trim($filedata[13]);
            $csvArr[$i]['package_detail'] = trim($filedata[14]);
            $csvArr[$i]['renewal'] = trim($filedata[15]);
            $csvArr[$i]['etc'] = trim($filedata[16]);
            $csvArr[$i]['discontinued'] = trim($filedata[17]);
            $csvArr[$i]['display'] = trim($filedata[18]);
          }
          $i++;
        }
        fclose($file);
        // echo "file_exist ".is_file($file);

        $count = 0;
        foreach($csvArr as $userdata) {
          $findProduct = $this->products
                            ->where('barcode', $userdata['barcode'])
                            ->where(['name' => $userdata['name'], 'name_en' => $userdata['name_en']])
                            ->where('spec', $userdata['spec'])
                            ->where('type', $userdata['type'])
                            ->countAllResults();
          // $findProduct = $this->products->where('barcode', $userdata['barcode'])->get()->getRow();
          if ( $findProduct == 0 ) {
          // if ( isset($findProduct) ) {
            if ( $this->products->insert($userdata)) { 
              $productId = $this->products->getInsertID();
              $count++;
            }
          // } else {
          //   // $this->products->set('')
          //   $this->products->where('id', $findProduct['id']);
          //   $this->products->update($userdata);
          //   // $getProducts = $this->products->where(['brand_id' => $userdata['brand_id'], 'barcode' => $userdata['barcode']])->get()->getRow();

          //   // $getProducts['']
            
          //   // foreach($getProducts as $getProduct) :
          //   // endforeach;
          }
        }
        echo $this->products->getLastQuery();

      //   session()->setFlashdata('message', $count.' rows successfully added');
      //   session()->setFlashdata('alert-class', 'alert-success');
      // } else {
      //   session()->setFlashdata('message', 'CSV file could not be imported');
      //   session()->setFlashdata('alert-class', 'alert-danger');
      }
    } 

    // return redirect()->back()->withInput();
  }

  public function exportData($fileType = 'csv') {
    $products = $this->products->product()
                      ->select(['id', 'barcode', 'name', 'name_en', 'spec', 'sample'])
                      ->where(['discontinued' => 0, 'display' => 1])
                      // ->whereIn(['brand_id' => $array])
                      ->get()->getResultArray();
    
    $fileName = date('Ymd_his').".$fileType";
    header("Content-Description: File Transfer"); 
    header("Content-Disposition: attachment; filename=$fileName"); 
    header("Content-Type: application/$fileType;charset=UTF-8;");
    header('Expires: 0');header('Content-Transfer-Encoding: binary');
    header('Cache-Control: private, no-transform, no-store, must-revalidate');
    
    echo "\xEF\xBB\xBF"; // 꼭 쓰기

    $file = fopen('php://output', 'w'); // output
    
    $header = array("index", "Barcode", "product name", "product name(en)", "spec", "sample", "공급가(입고가)");
    fputcsv($file, $header);
    foreach($products as $product) :
      fputcsv($file, $product);
    endforeach;
    fclose($file);
    exit;
    // fputs // fwrite
  }
}
