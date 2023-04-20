<?php
namespace App\Models;

use CodeIgniter\Model;

class CurrencyRateModel extends Model {
  protected $table = 'currency_rate';
  protected $primaryKey = 'cRate_idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'currency_idx', 'exchange_rate', 'default_set', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created';
  protected $updatedField = 'updated';
  protected $dateFormat = 'datetime';
}