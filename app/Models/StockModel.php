<?php
namespace App\Models;
use CodeIgniter\Model;

class StockModel extends Model {
  protected $table = 'stocks';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'prd_id', 'order_base', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $deletedField = 'deleted_at';
  protected $dateFormat = "datetime";
}