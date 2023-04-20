<?php
namespace App\Models;

use CodeIgniter\Model;

class ShipmentModel extends Model {
  protected $table = 'shipment';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'shipment_name', 'shipment_name_en', 'avaliable'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $dateFormat = 'datetime';

  public function joinDelivery() {
    return $this->join('delivery', 'delivery.shipment_id = shipment.id', 'left outer')
                ->where("{$this->table}.available", 1);
  }
}