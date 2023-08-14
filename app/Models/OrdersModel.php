<?php
namespace App\Models;

use CodeIgniter\Model;

class OrdersModel extends Model {
  protected $table = 'orders';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'buyer_id', 'order_number', 'complete_payment',
    'request_amount', 'inventory_fixed_amount',
    'order_amount', 'discount_amount', 'subtotal_amount', 
    'currency_rate_idx', 'calc_currency_rate_id', 'currency_code', 
    'order_check', 'change_order_id',
    'payment_id', 'address_id'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';

  public function orderJoin() {
    $this->select("{$this->table}.*")
        ->select('currency_rate.exchange_rate')
        ->select('currency.currency_sign AS currency_sign')
        ->select('currency.currency_float AS currency_float')
        ->join("currency_rate", "currency_rate.cRate_idx = {$this->table}.currency_rate_idx", "left outer")
        ->join("currency", "currency.idx = currency_rate.currency_idx", "left outer");

    return $this;
  }

  public function packagingJoin() {
    return $this->join('packaging', 'packaging.order_id = orders.id')
                ->join('packaging_detail', 'packaging_detail.packaging_id = packaging.idx AND packaging_detail.in_progress = 1 AND packaging_detail.complete = 0')
                ->join('packaging_status', 'packaging_status.idx = packaging_detail.status_id');
  }

  public function productWeight() {
    return $this->select('CAST(IFNULL(prd_weight.shipping_weight, 0) AS DOUBLE) AS shipping_weight')
                ->join('( SELECT orders_detail.order_id, SUM(product.shipping_weight) AS shipping_weight
                          FROM product
                            JOIN orders_detail ON orders_detail.prd_id = product.id
                          GROUP BY orders_detail.order_id) AS prd_weight'
                        , 'prd_weight.order_id = orders.id', 'RIGHT')
                ->join('( SELECT order_id, GROUP_CONCAT(receipt_type, ":", payment_status order by receipt_id) AS payment_status_group 
                        FROM orders_receipt GROUP BY order_id ORDER BY receipt_id) AS receipt_group', 'receipt_group.order_id = orders.id', 'left outer');
  }

  public function deliveryJoin() {
    return $this->select('CAST(delivery.delivery_price AS DOUBLE) AS delivery_price')
                // ->select('SUM(CAST(IFNULL(delivery.delivery_price, 0) AS DOUBLE)) AS delivery_price')
                ->join("( SELECT order_id, SUM(delivery_price) AS delivery_price
                          FROM delivery 
                          GROUP BY delivery.order_id ) AS delivery"
                      , 'delivery.order_id = orders.id'
                      , 'RIGHT');
  }

  public function paymentJoin() {
    return $this->join('payment_method', 'payment_method.id = orders.payment_id', 'LEFT OUTER');
  }
  
  public function buyerJoin() {
    return $this->join('buyers', 'buyers.id = orders.buyer_id')
                ->join('users', 'users.buyer_id = buyers.id')
                ->join('manager', 'manager.idx = buyers.manager_id')
                // ->join('delivery', 'delivery.order_id = orders.id AND delivery.delivery_code = 100', 'left outer')
                ->join('buyers_address', 'buyers_address.idx = orders.address_id')
                ->join("orders_receipt", "orders_receipt.order_id = orders.id", "left outer");
  }
}