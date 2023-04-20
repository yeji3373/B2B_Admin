<?php
namespace App\Models;

use CodeIgniter\Model;

class CurrencyModel extends Model {
  protected $table = 'currency';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'currency_code', 'currency_sign', 'currency_float', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $dateFormat = 'datetime';

  public function currency() {
    return $this->join('currency_rate', 'currency_rate.currency_idx = currency.idx', 'left outer')
                ->where("{$this->table}.available", 1);
  }
}