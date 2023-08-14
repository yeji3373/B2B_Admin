<?php
namespace App\Models;

use CodeIgniter\Model;

class EmailModel extends Model {
  protected $table = 'email';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;
  protected $useAutoIncrement = true;

  protected $allowedFields = [];

  protected $useTimestamps = false;
  protected $dateFormat    = 'datetime';
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';

  protected $allowCallbacks = true;
  protected $beforeInsert   = [];
  protected $afterInsert    = [];
  protected $beforeUpdate   = [];
  protected $afterUpdate    = [];
  protected $beforeFind     = ['beforeFindMethod'];
  protected $afterFind      = [];
  protected $beforeDelete   = [];
  protected $afterDelete    = [];

  public function beforeFindMethod(Array $data) {
    return $data;
  }
}