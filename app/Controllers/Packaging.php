<?php
namespace App\Controllers;

use App\Models\DeliveryModel;
use App\Models\PackagingModel;
use App\Models\PackagingDetailModel;
use App\Models\PackagingStatusModel;

class Packaging extends BaseController {
  public function __construct() {
    $this->delivery = new DeliveryModel();
    $this->packaging = new PackagingModel();
    $this->packagingDetail = new PackagingDetailModel();
    $this->packagingStatus = new PackagingStatusModel();
  }

  public function packagingRequest() {
    $data = $this->request->getPost();
    // $packaging_id = null;

    if ( !empty($data) ) {
      $packStatus;// = $this->packagingStatus->where(['available'=> 1, 'order_by' => 1])->first();
      $saveCondition = array();

      if ( empty($data['id']) ) { // packaging id 체크
        if ( !empty($data['order_id']) ) {
          if ( empty($this->packaging->where('order_id', $data['order_id'])->first()) ) {
            if ( $this->packaging->save($data) ) {
              $packaging_id = $this->packaging->getInsertID();
              $cnt = 0;
              $packStatus = $this->packagingStatus->where(['available'=> 1, 'order_by <' => 3])->orderBy('order_by ASC')->findAll();
              if ( !empty($packStatus) ) {
                print_r($packStatus);
                $cnt = count($packStatus) - 1;
                foreach( $packStatus AS $i => $status ) {
                  $saveCondition = ['packaging_id' => $packaging_id, 'status_id' => $status['idx']];

                  if ( empty($this->packagingDetail->where($saveCondition)->first()) ) {
                    array_merge($saveCondition, ['in_progress' => 1]);
                    if ( $i < $cnt ) array_merge($saveCondition, ['complete' => 1]);
                    
                    if ( !$this->packagingDetail->save($saveCondition)) {
                      // session()->setFlashdata('error', 'packaging dettail input error');
                      // return;
                      return redirect()->back()->with('error', 'packaging dettail input error');
                    }
                  }
                }
              } else {
                // session()->setFlashdata('error', 'packaging status error'); 
                // return ;
                return redirect()->back()->with('error', 'packaging status error');
              }
            } else {
              // session()->setFlashdata('error', 'packaing input error'); 
              // return;
              return redirect()->back()->with('error', 'packaing input error');
            }
          // } else { // packaging list 있음
          }
        } else {
          // session()->setFlashdata('error', '일치하는 주문 정보가 없음');
          // return; // packaging 리스트도 없고 order 항목도 없음
          return redirect()->back()->with('error', '일치하는 주문 정보가 없음');
        }
      } else {
        $saveCondition['packaging_id'] = $data['id'];
        $saveCondition['idx'] = $data['detail_id'];

        $detail = $this->packagingDetail->where($saveCondition)->first();

        if ( empty($detail) ) {
          // session()->setFlashdata('error', '포장 요청사항이 없습니다.');
          // return;
          return redirect()->back()->with('error', '포장 요청사항이 없습니다.');
        } else {
          if ( $detail['in_progress'] == 1 && $detail['complete'] == 0 )  {
            unset($saveCondition['idx']);
            $status = $this->packagingStatus
                          ->select('packaging_status.*')
                          ->select('next_status.status_id AS next_status_id')
                          ->join('( SELECT idx, (LEAD(idx) OVER(ORDER BY order_by)) AS status_id
                                    FROM packaging_status) AS next_status'
                                  , 'next_status.idx = packaging_status.idx'
                                  , 'left outer')
                          ->where(['packaging_status.idx' => $detail['status_id'], 'packaging_status.available' => 1])
                          ->orderBy('packaging_status.order_by ASC')
                          ->first();
            // print_r($status);
            if ( !empty($status) ) {
              if ($this->packagingDetail->save(['idx' => $detail['idx'], 'complete' => 1]) ) {
                if ( !empty($status['next_status_id']) ) {
                  $saveCondition['in_progress'] = 1;
                  $saveCondition['status_id'] = $status['next_status_id'];
                  // $this->packagingDetail->save($saveCondition);
                  if ( !$this->packagingDetail->save($saveCondition) ) {
                    return redirect()->back()->with('error', '배송현황 요청 처리중 오류 발생');
                  }
                }
              }
            }
          }
          // print_r($detail);
          return redirect()->back();
        }
      }
    } else return;
  }
}
