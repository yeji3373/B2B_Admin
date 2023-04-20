<?php
namespace App\Models;
use CodeIgniter\Model;

class SupplyPriceModel extends Model {
  protected $table = 'supply_price';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'product_idx', 'product_price_idx', 'margin_idx',
    'margin_level','price', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = "datetime";
}