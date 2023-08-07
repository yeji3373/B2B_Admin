<?php
namespace App\Models;

use CodeIgniter\Model;

class RequirementModel extends Model {
  protected $table = 'requirement';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;
  
  protected $allowedFields = [
    'requirement_kr', 'requirement_en', 'placeholder',
    'display'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $dateFormat = 'datetime';
}