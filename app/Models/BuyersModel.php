<?php
namespace App\Models;

use CodeIgniter\Model;

class BuyersModel extends Model {
  protected $table = 'buyers';
  protected $primaryKey = 'id';
  protected $useAutoIncrement = true;
  protected $useSoftDeletes = true;

  protected $allowedFields = [
    'name', 'manager_id', 'business_number', 
    'region_ids', 'countries_ids', 
    'address', 'city', 'country_id', 'zipcode', 'phone',
    'certificate_business', 'tax_check', 'margin_level',
    'deposit_rate', 'confirmation', 'available'
  ];
  
  protected $useTimestamps = true;
  protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
  protected $deletedField  = 'deleted_at';
  protected $dateFormat  	 = 'datetime';

  protected $default = ['available' => 1];

  public function buyers() {
    return $this->where($this->default);
  }

  public function buyerJoin() {
    if ( !empty(session()->userData['buyerId']) ) {
      $this->default[$this->table.'.id'] = session()->userData['buyerId'];
    }
    return $this
            ->join('buyers_currency', 'buyers_currency.buyer_id = '.$this->table.'.id')
            ->join('currency_rate', 'currency_rate.cRate_idx = buyers_currency.cRate_idx', 'left outer')
            ->join('currency', 'currency.idx = buyer_currency.currecy_idx', 'left outer')
            // ->join('currency_code', 'currency_code.idx = currency.currency_code_idx');
            ->where($this->default);
  }
}