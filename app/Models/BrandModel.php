<?php
namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model {
  protected $table = 'brand';
  protected $primaryKey = 'brand_id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'brand_name', 'brand_logo_src', 'own_brand', 
    'excluded_countries', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'brand_registration_date';
  protected $updatedField = '';
  protected $dateFormat = 'datetime';

  public function brands() {
    return $this->select('brand.*')
                ->select('brand_opts.supply_rate_based, brand_opts.supply_rate_by_brand')
                ->select('brand_opts.available AS brand_opt_available')
                ->join('brand_opts', 'brand_opts.brand_id = brand.brand_id', 'left outer')
                ->orderBy('brand.own_brand DESC');
                // ->orderBy('brand.brand_id ASC');

  }
}