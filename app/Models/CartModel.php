<?php
namespace App\Models;

use CodeIgniter\Model;

class CartModel extends Model {
  protected $table = 'cart';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'buyer_id', 'brand_id', 'prd_id', 'onlyZeroTax', 'chkd',
    'stock_req', 'stock_req_parent', 'order_qty_changed',
    'supply_price_idx', 'product_price_idx', 'supply_price_changed',
    'prd_price', 'order_qty', 'order_price', 'margin_section_id',
    'prd_section', 'dis_section_margin_rate_id', 'dis_section',
    'dis_rate', 'dis_prd_price', 'apply_discount'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}