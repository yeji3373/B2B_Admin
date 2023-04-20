<?php
namespace App\Models;

use CodeIgniter\Model;

class BuyersCurrencyModel extends Model {
  protected $table = 'buyers_currency';
  protected $primaryKey = 'id';
  protected $useAutoIncrement = true;
  protected $useSoftDeletes = true;

  protected $allowedFields = [
    'buyer_id', 'cRate_idx', 'available'
  ];
  
  protected $useTimestamps = true;
  protected $createdField  = 'created_at';
  protected $updatedField  = '';
  protected $deletedField  = 'deleted_at';
  protected $dateFormat  	 = 'datetime';
}