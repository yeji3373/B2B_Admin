<?php
namespace App\Models;

use CodeIgniter\Model;

class MarginRateModel extends Model {
  protected $table = 'margin_rate';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'brand_id', 'margin_idx', 'margin_rate', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updateField = 'updated_at';
  protected $dateFormat = 'datetime';
}