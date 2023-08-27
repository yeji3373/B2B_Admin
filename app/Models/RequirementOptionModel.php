<?php
namespace App\Models;

use CodeIgniter\Model;

class RequirementOptionModel extends Model {
  protected $table = 'requirement_option';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'requirement_idx', 'option_name', 'option_name_en', 
    'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = '';
  protected $dateFormat = 'datetime';
}