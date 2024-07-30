<?php
namespace App\Models;

use CodeIgniter\Model;

class Cafe24IpSetModel extends Model {
	protected $table = 'cafe24_ip_set';
	protected $primaryKey = 'idx';
	protected $useAutoIncrement = true;

	protected $allowedFields = ['country_id', 'country_code', 'corp_idx', 'ip_allowance_yn', 'max_own_ips', 'max_other_ips'];

	protected $useTimestamps = true;
	protected $updatedField  = 'updated_at';
	protected $createdField  = 'created_at';

  public function getCountries($join = 'LEFT') {
    return $this
              ->select("{$this->table}.idx AS ip_set_idx, countries.*")
              ->join('countries', "countries.id = {$this->table}.country_id", $join)
              ->find();
  }
}