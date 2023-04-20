<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\StockModel;
use App\Models\StockDetailModel;
// use App\Models\StockReqModel;

class Stock extends BaseController {
  protected $data; 

  public function __construct() {
    $this->product = new ProductModel();
    $this->stock = new StockModel();
    $this->stockDetail = new StockDetailModel();
    
    $this->data['header'] = ['css' => ['/table.css', '/product/product.css']
                              ,'js' => ['/product/product.js']];
  }

  public function index() {
    /* 재고 등록 시, detail에 입고일에 얼마나 들어왔는지 입력되고, 
      stock에 통합된 재고 입력하기
    */
    $search = $this->request->getGet();

    $orderBy = empty($search['orderBy']) ? 'stocks.prd_id' : $search['orderBy'];
    $pageCnt = empty($search['pageCnt']) ? 50 : $search['pageCnt'];

    $this->data['productStocks'] = $this->product
                                    ->select('product.id AS product_id, product.barcode, product.productCode
                                              , product.img_url, product.name, product.name_en
                                              , product.type, product.type_en
                                              , product.shipping_weight')
                                    ->select('brand.brand_name')
                                    ->select('stocks.order_base, stocks.available AS stocks_avaliable')
                                    ->select('stocks_detail.supplied_qty, stocks_detail.layout_section
                                              , stocks_detail.available AS stocks_detail_available
                                              , stocks_detail.exp_date, stocks_detail.supplied_date')
                                    ->select('stock_req.req_qty')
                                    ->join('brand', 'product.brand_id = brand.brand_id')
                                    ->join('stocks', 'stocks.prd_id = product.id')
                                    ->join('stocks_detail', 'stocks.id = stocks_detail.stocks_id')
                                    ->join('(SELECT stock_id, SUM(req_qty) AS req_qty FROM stocks_req GROUP BY stock_id) AS stock_req', 'stock_req.stock_id = stocks_detail.id', 'left outer')
                                    ->where('stocks.available', 1)
                                    ->where('stocks_detail.available', 1)
                                    ->orderBy($orderBy)
                                    ->paginate($pageCnt);

    $this->data['pager'] = $this->product->pager;
    return $this->menuLayout('stock/main', $this->data);
  }
}