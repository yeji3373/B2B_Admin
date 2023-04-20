<?php
namespace App\Models;

use CodeIgniter\Model;

class PackagingModel extends Model {
  protected $table = 'packaging';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'order_id', 'showed'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}