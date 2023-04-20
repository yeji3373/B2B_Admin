<?php
namespace App\Models;
use CodeIgniter\Model;

class CountryModel extends Model {
  protected $table = 'countries';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'region_id', 'name', 'name_en', 'country_code'
  ];

  protected $useTimestamps = false;
}