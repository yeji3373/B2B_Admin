<?php
namespace App\Models;

use CodeIgniter\Model;

class Cafe24Model extends Model {
	protected $table = 'cafe24_available_ip';
	protected $primaryKey = 'idx';
	protected $useAutoIncrement = true;

	protected $allowedFields = ['ip', 'own_ip', 'ip_nation', 'corp_name'];

	protected $useTimestamps = true;
	protected $updatedField  = 'updated_at';

	public function getCafe24IpByPages() {
		return $this->select('cafe24_available_ip.*, countries.name, countries.name_en')
									->join('countries', 'countries.country_code = cafe24_available_ip.ip_nation');
	}

	public function getCountries() { 
		return $this->select('cafe24_available_ip.ip_nation, countries.name, countries.name_en')
									->join('countries', 'countries.country_code = cafe24_available_ip.ip_nation')
									->where('cafe24_available_ip.ip_nation is not null and cafe24_available_ip.ip_nation != ""')
									->groupby('cafe24_available_ip.ip_nation')
									->findAll();
	}
}