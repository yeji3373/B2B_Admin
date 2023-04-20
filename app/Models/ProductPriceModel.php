<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductPriceModel extends Model {
  protected $table = 'product_price';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'product_idx', 'retail_price', 'supply_price',
    'supply_rate_applied', 'supply_rate',
    'not_calculating_margin',
    'taxation', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}