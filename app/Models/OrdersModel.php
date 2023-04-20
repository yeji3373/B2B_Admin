<?php
namespace App\Models;

use CodeIgniter\Model;

class OrdersModel extends Model {
  protected $table = 'orders';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'buyer_id', 'order_number', 'complete_payment',
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
    $this
      ->select("{$this->table}.*")
      ->select('currency_rate.exchange_rate')
      ->select('IFNULL(currency.currency_sign, "$") AS currency_sign')
      ->select('IFNULL(currency.currency_float, 2) AS currency_float')
      ->select('payment_method.payment')
      // ->select('orders_receipt.receipt_type, orders_receipt.payment_status')
      ->join("currency_rate", "currency_rate.cRate_idx = {$this->table}.calc_currency_rate_id", "left outer")
      ->join("currency", "currency.idx = currency_rate.currency_idx", "left outer")
      ->join("payment_method", "payment_method.id = {$this->table}.payment_id");
      // ->join("orders_receipt", "orders_receipt.order_id = {$this->table}.id", "left outer");

    return $this;
  }
}