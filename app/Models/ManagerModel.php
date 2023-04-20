<?php
namespace App\Models;

use CodeIgniter\Model;

class ManagerModel extends Model {
  protected $table = 'manager';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'rol_id', 'region_id', 'id', 'name', 'email', 'contact_us', 'active'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updateField = 'updated_at';
  protected $dateFormat = 'datetime';
}