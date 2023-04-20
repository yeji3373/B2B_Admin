<?php
namespace App\Models;

use CodeIgniter\Model;

class MarginModel extends Model {
  protected $table = 'margin';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'margin_level', 'margin_section', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updateField = 'updated_at';
  protected $dateFormat = 'datetime';
}