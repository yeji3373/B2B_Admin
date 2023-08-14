<?php
namespace App\Models;

use CodeIgniter\Model;

class OrderDetailModel extends Model {
  protected $table = 'orders_detail';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    // 'buyer_id', 'order_number',  // 교차할때 필요한 컬럼 생성 후 추가
    'order_id', 'order_excepted',  'prd_id', 
    'stock_req', 'stock_req_qty', 
    'prd_change_qty', 'prd_qty_changed',
    'prd_change_price', 'prd_price_changed',
    'changed_manager', 'prd_discount',
    'margin_rate_id', 'status_id', 'expiration_date',
    'detail_desc'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';

  public function productBrandJoin() {
    $this->select("{$this->table}.*")
        ->select('brand.brand_name')
        ->select('product.id AS product_idx,
                  product.barcode, product.productCode,
                  product.hs_code, product.sample,
                  product.name AS prd_name, product.name_en AS prd_name_en,
                  product.type, product.type_en, product.box, product.in_the_box,
                  product.contents_of_box, product.spec, product.spec2,
                  product.container')
        ->join('product', 'product.id = orders_detail.prd_id')
        ->join('brand', 'brand.brand_id = product.brand_id');
    return $this;
  }

  public function detailReasonJoin() {
    return $this->join("order_detail_details', 'order_detail_details.detail_id = {$this->table}.id")
                ->join('order_reason', 'order_reason.idx = order_detail_details.detail_reason_idx');
  }
}