<?php
namespace App\Models;

use CodeIgniter\Model;

class RequirementRequestModel extends Model {
  protected $table = 'requirement_request';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;
  
  protected $allowedFields = [
    'order_id', 'requirement_id', 'requirement_detail',
    'requirement_reply', 'requirement_check'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = 'datetime';

  public function requirement($where = []) {
    return $this->join('requirement', 'requirement.idx = requirement_request.requirement_id')
                ->join('orders_detail', 'orders_detail.id = requirement_request.order_detail_id')
                ->where($where);
  }
}