<?php
namespace App\Models;

use CodeIgniter\Model;

class DeliveryModel extends Model {
  protected $table = 'delivery';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'order_id', 'forward', 'shipment_id', 'payment_id', 'delivery_currency_idx', 'delivery_price',
    'ci_number', 'delivery_status', 'delivery_status_en', 'delivery_code',
    'packaging_id'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}