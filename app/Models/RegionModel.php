<?php
namespace App\Models;
use CodeIgniter\Model;

class RegionModel extends Model {
  protected $table = 'region';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'region', 'region_en'
  ];

  protected $useTimestamps = false;
}