<?php
namespace App\Models;

use CodeIgniter\Model;

class PayPalModel extends Model {
  protected $table = 'paypal'; // 임시 invoce 발급 관리용
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'manager_id', 'paypal_id', 'invoice_url', 'invoice_number',
    'buyer_email', 'invoice_status', 'amount', 'due_amount', 'sandbox'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}