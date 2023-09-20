<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductSpqModel extends Model {
  protected $table = 'product_spq';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'product_idx', 'moq', 'spq', 'spq_criteria', 'spq_inBox', 'spq_outBox', 
    'calc_code', 'calc_unit', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = "datetime";
}