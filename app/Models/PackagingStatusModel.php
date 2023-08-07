<?php
namespace App\Models;

use CodeIgniter\Model;

class PackagingStatusModel extends Model {
  protected $table = 'packaging_status';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'order_by', 'status_name', 'status_name_en',
    'display', 'invoice_display', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';

  public function packagingStatus($orderId = NULL) {
    if ( !empty($orderId) ) {
      $this->join("( SELECT packaging.idx, packaging.order_id
                          , packaging_detail.packaging_id, packaging_detail.status_id
                          , packaging_detail.in_progress, packaging_detail.complete
                      FROM packaging 
                      LEFT OUTER JOIN packaging_detail ON packaging.idx = packaging_detail.packaging_id
                      WHERE packaging.order_id = {$orderId} ) AS packaging"
                  , "packaging.status_id = packaging_status.idx", "left outer");
    }
    return $this;
  }
}