<?php
namespace App\Models;

use CodeIgniter\Model;

class PackagingModel extends Model {
  protected $table = 'packaging';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'order_id', 'showed'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';

  public function packaging() {
    return $this->select("{$this->table}.*")
                ->select("packaging_detail.idx AS detail_idx, packaging_detail.*")
                ->select("packaging_status.*")
                ->join('packaging_detail', 'packaging_detail.packaging_id = packaging.idx')
                ->join('packaging_status', 'packaging_status.idx = packaging_detail.status_id')
                ->where('packaging_status.available', 1);
  }
}