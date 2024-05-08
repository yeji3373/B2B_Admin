<?php
namespace App\Models;

use CodeIgniter\Model;

class MarginModel extends Model {
  protected $table = 'margin';
  protected $primaryKey = 'idx';
  protected $useSoftDeletes = false;

  protected $allowedFields = [
    'margin_level', 'margin_section', 'available'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updateField = 'updated_at';
  protected $dateFormat = 'datetime';

  // // protected $allowCallbacks = true;

  // protected $beforeInsert = ['transaction'];
  // protected $afterInsert = ['afterInsertTest'];

	// protected $beforeUpdate = ['transaction'];
  // protected $afterUpdate = ['transaction'];

  // // public $returnData;

  // // public function __construct() {
  // //   $this->transStrict(false);
  // // }

  // // function saved(...$param) {
  // //   $this->save($param);
  // // }

  // function updateMargin() {
  //   if ( $this->transStatus() ) {
  //     return $this->returnData;
  //   } else return 'false';
  // }

  // function saved(...$param) {
  //   $this->save($param);

  //   if ( !$this->transStatus() ) {
  //     $this->transRollback();
  //   } else {
  //     $this->transCommit();
  //   }
  //   // return $this->getInsertID();
  // }

  // function afterInsertTest() {
  //   // return 
  // }

  // protected function transaction() {
  //   $this->stransStrict(false);
  //   $this->transBegin();

  //   // if ( !$this->transStatus() ) {
  //   //   $this->transRollback();
  //   // } else {
  //   //   $this->transCommit();
  //   // }
  // }
}