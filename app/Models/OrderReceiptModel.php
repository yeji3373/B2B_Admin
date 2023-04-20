<?php
namespace App\Models;

use CodeIgniter\Model;

class OrderReceiptModel extends Model {
  protected $table = 'orders_receipt';
  protected $primaryKey = 'receipt_id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'order_id', 'receipt_type', 'payment_status', 'payment_date',
    'payment_invoice_id', 'payment_invoice_number',
    'payment_refund_id', 'refund_date', 'payment_url', 'delivery_id',
    'rq_percent', 'rq_amount', 'due_amount', 'display'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';
}