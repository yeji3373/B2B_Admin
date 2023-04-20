<?php
namespace App\Models;
use CodeIgniter\Model;

class StockDetailModel extends Model {
  protected $table = 'stocks_detail';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'stocks_id', 'supplied_qty', 'layout_section', 'available',
    'manager_id', 'exp_date'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'supplied_date';
  protected $updatedField = 'updated_at';
  // protected $deletedField = 'deleted_at';
  protected $dateFormat = "datetime";
}