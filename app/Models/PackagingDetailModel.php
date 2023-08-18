<?php
namespace App\Models;

use CodeIgniter\Model;

class PackagingDetailModel extends Model {
  protected $table = 'packaging_detail';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'packaging_id', 'status_id', 'in_progress', 'complete', 'send_email'
  ];

  protected $useTimestamps = true;
  protected $dateFormat = 'datetime';
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  // protected $deletedField = 'deleted_at';

  public function packagingDetailWhere($where = []) {
    return $this->where($where);
  }

  public function packagingDetailJoinStatus($where = []) {
    $this->select("{$this->table}.*")
        ->select('packaging_status.order_by, packaging_status.status_name')
        ->join('packaging_status', "packaging_status.idx = {$this->table}.status_id")
        ->where($where)
        // ->where("{$this->table}.deleted_at", NULL)
        ->orderBy('packaging_status.order_by DESC');
    
    return $this; 
  }
}