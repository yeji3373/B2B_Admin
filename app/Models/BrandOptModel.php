<?php
namespace App\Models;

use CodeIgniter\Model;

class BrandOptModel extends Model {
  protected $table = 'brand_opts';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'brand_id','supply_rate_based', 'supply_rate_by_brand',
    'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}