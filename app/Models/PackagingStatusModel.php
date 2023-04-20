<?php
namespace App\Models;

use CodeIgniter\Model;

class PackagingStatusModel extends Model {
  protected $table = 'packaging_status';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'order_by', 'status_name', 'status_name_en',
    'display', 'invoice_display', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}