<?php
namespace App\Models;
use CodeIgniter\Model;

class UsersModel extends Model {
  protected $table = 'users';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'buyer_id', 'id', 'name', 'email', 'active'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = "datetime";
}