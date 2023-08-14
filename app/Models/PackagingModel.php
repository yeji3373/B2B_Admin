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
  protected $dateFormat = 'datetime';
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';  

  protected $allowCallbacks = false;
  protected $beforeInsert   = [];
  protected $afterInsert    = [];
  protected $beforeUpdate   = [];
  protected $afterUpdate    = [];
  protected $beforeFind     = ['beforeFindMethod'];
  protected $afterFind      = [];
  protected $beforeDelete   = [];
  protected $afterDelete    = [];

  public $where = [];
  public $orderby = '';

  public function beforeFindMethod(Array $data) {
    return $data;
  }

  public function packaging(Array $sql) {
    if ( array_key_exists('where', $sql) ) $this->where = $sql['where'];
    if ( array_key_exists('orderBy', $sql) ) $this->orderby = $sql['orderBy'];

    $this->select("{$this->table}.*")
          ->select("packaging_detail.idx AS detail_idx, packaging_detail.*")
          ->select("packaging_status.*")
          ->join('packaging_detail', 'packaging_detail.packaging_id = packaging.idx')
          ->join('packaging_status', 'packaging_status.idx = packaging_detail.status_id')
          ->where($this->where)
          ->orderBy($this->orderby);

    return $this;
  }
}