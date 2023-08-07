<?php
namespace App\Models;

use CodeIgniter\Model;

class OrderStatusModel extends Model {
  protected $table = 'order_status';
  protected $primaryKey = 'status_id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $dateFormat = 'datetime';
}