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

  protected $beforeFind = ['beforeFindMethod'];

  public $where = [];
  public $orderBy = '';

  protected function beforeFindMethod(Array $data) {
    log_message('info', json_encode($data));
    return $data;
  }  
  
  public function requirement(Array $data) {
    if ( array_key_exists('where', $data) ) $this->where = $data['where'];
    if ( array_key_exists('orderBy', $data) ) $this->orderby = $data['orderBy'];

    return $this->select("{$this->table}.*")
                ->select('requirement.requirement_en, requirement.requirement_kr, requirement.placeholder')
                ->join('requirement', 'requirement.idx = requirement_request.requirement_id')
                ->join('orders_detail', 'orders_detail.id = requirement_request.order_detail_id')
                ->where($this->where);
  }
}