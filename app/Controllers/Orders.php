<?php
namespace App\Controllers;

use App\Models\CurrencyModel;
use App\Models\OrdersModel;
use App\Models\OrderDetailModel;
use App\Models\OrderStatusModel;
use App\Models\RequirementRequestModel;
use App\Models\OrderReceiptModel;
use App\Models\ShipmentModel;
use App\Models\DeliveryModel;
use App\Models\ProductModel;
use App\Models\PackagingModel;
use App\Models\PackagingDetailModel;
use App\Models\PackagingStatusModel;

use App\Models\PayPalModel; // 임시. invoce 발급 및 관리 목적
use App\Models\ManagerModel;

use Paypal\Controllers\PaypalController;
use Paypal\Config\Paypal;

use App\Controllers\Packaging;

use Status\Config\Status;

class Orders extends BaseController {
  public function __construct() {
    $pager = service('pager');
    $this->status = config('Status');
    $this->currency = new CurrencyModel();
    $this->order = new OrdersModel();
    $this->orderDetail = new OrderDetailModel();
    $this->orderStatus = new OrderStatusModel();
    $this->requirementRequest = new RequirementRequestModel();
    $this->receipt = new OrderReceiptModel();
    $this->shipment = new ShipmentModel();
    $this->delivery = new DeliveryModel();
    $this->product = new ProductModel();
    $this->packaging = new PackagingModel();
    $this->packagingStatus = new PackagingStatusModel();
    $this->packagingDetail = new PackagingDetailModel();

    $this->paypalModel = new PayPalModel(); // 임시. invoice 발급 및 관리 목적
    $this->managerModel = new ManagerModel();
    
    $this->PaypalController = new PaypalController();
    $this->PaypalConfig = new Paypal();

    $this->packagingController = new Packaging();

    $this->data['header'] = ['css' => ['/orders/orders.css', '/table.css', '/inputLabel.css']
                            , 'js' => ['/orders/orders.js']];
    
    $this->data['status'] = $this->status;
  }

  public function index() {
    $params = $this->request->getVar();
    if ( !empty($params) ) {
      if ( !empty($params['order_number']) ) {
        $this->order->like('orders.order_number', $this->request->getVar('order_number'), 'both');
      }

      if ( !empty($params['order_status']) ) {
        $this->order->where('order_status.status_id', $params['order_status']);
      }
    }

    $this->data['orderStatus'] = $this->orderStatus->where('available', 1)->orderBy('status_id')->findAll();
    $this->data['orders'] = $this->getOrders()
                              ->where('orders.available', 1)
                              ->orderBy('orders.id DESC')
                              ->paginate(15);
    $this->data['orderPager'] = $this->getOrders()->pager;
    
    return $this->menuLayout('orders/main', $this->data);
  }

  public function detail() {
    $orderId = $this->request->uri->getSegment(3);
    $this->data['currency'] = $this->currency->where('available', 1)->find();
    $this->data['order'] = $this->getOrder($orderId)->first();
    $this->data['details'] = $this->getOrderDetail($orderId)->findAll();
    $this->data['receipts'] = $this->receipt->select('orders_receipt.*, delivery.delivery_price')->join('delivery', 'delivery.id = orders_receipt.delivery_id', 'left outer')->where('orders_receipt.order_id', $orderId)->findAll();
    $this->data['shipments'] = $this->shipment->findAll();
    // $this->data['deliveries'] = $this->delivery
    //                                 ->select('delivery.*, IFNULL(orders_receipt.delivery_id, 0) AS receipt_included')
    //                                 ->join('orders_receipt', 'orders_receipt.delivery_id = delivery.id', 'left outer')
    //                                 ->where('delivery.order_id', $orderId)
    //                                 ->findAll();
    $this->data['deliveries'] = $this->delivery->where('delivery.order_id', $orderId)->findAll();
    $this->data['packaging'] = $this->packaging
                                      ->select('packaging.*')
                                      ->select('packaging_status.idx AS packaging_status_idx')
                                      ->select('packaging_status.order_by AS packaging_status_order_by')
                                      ->select('packaging_status.status_name AS status_name')
                                      ->select('packaging_status.status_name_en AS status_name_en')
                                      ->select('packaging_status.display AS packaging_status_display')
                                      ->select('packaging_status.available AS packaging_status_available')
                                      ->select('packaging_detail.idx AS detail_idx')
                                      ->select('packaging_detail.status_id')
                                      ->select('packaging_detail.complete')
                                      ->select('packaging_detail.in_progress')
                                      ->select('( SELECT status_name
                                                  FROM packaging_status
                                                  WHERE order_by > packaging_status_order_by AND available = 1
                                                  ORDER BY order_by ASC
                                                  LIMIT 1
                                                ) AS next_status_name')
                                      ->join('packaging_detail', 'packaging_detail.packaging_id = packaging.idx', 'left outer')
                                      ->join('packaging_status', 'packaging_status.idx = packaging_detail.status_id', 'left outer')
                                      ->where('packaging.order_id', $orderId)
                                      ->orderBy('packaging_status.order_by DESC')
                                      ->first();

    // $this->data['packagingStatus'] = $this->packagingStatus
    //                                       ->select('packaging_status.*')
    //                                       ->select('packaging.complete')
    //                                       ->select('packaging.in_progress')
    //                                       ->join("( SELECT packaging.idx, packaging.order_id
    //                                                       , packaging_detail.packaging_id, packaging_detail.status_id
    //                                                       , packaging_detail.in_progress, packaging_detail.complete
    //                                                 FROM packaging 
    //                                                 LEFT OUTER JOIN packaging_detail ON packaging.idx = packaging_detail.packaging_id
    //                                                 WHERE packaging.order_id = {$orderId}
    //                                               ) AS packaging"
    //                                               , "packaging.status_id = packaging_status.idx", "left outer")
    //                                       ->where(['packaging_status.available' => 1])
    //                                       ->orderBy('packaging_status.order_by ASC')
    //                                       ->findAll();
    $this->data['packagingStatus'] = $this->packagingStatus->packagingStatus($orderId)
                                          ->select('packaging_status.*')
                                          ->select('packaging.complete')
                                          ->select('packaging.in_progress')
                                          ->where(['packaging_status.available' => 1])
                                          ->orderBy('packaging_status.order_by ASC')
                                          ->findAll();

    if ( !empty($this->data['receipts']) ) {
      foreach($this->data['receipts'] as $i => $receipt) { 
        if ( $receipt['payment_status'] == 0 ) {
          if ( !is_null($receipt['payment_invoice_id']) ) {
            $updateData = [];
            $paypalDetail = $this->PaypalController->showInvoiceDetail($receipt['payment_invoice_id']);
            
            // print_r($paypalDetail);

            if ( $paypalDetail['data']['due_amount']['value'] == 0 && ($paypalCancelResult['data']['status'] == 'PAID' || $paypalCancelResult['data']['status'] == 'MARKED_AS_PAID') ) {
              if ( is_null($receipt['payment_invoice_number']) ) {
                $updateData['payment_invoice_number'] = $paypalDetail['data']['detail']['invoice_number'];
              }
              $updateData['payment_date'] = $paypalDetail['data']['payments']['transactions'][0]['payment_date'];
              $updateData['payment_status'] = 100;
              $this->receipt->where('receipt_id', $receipt['receipt_id'])->set($updateData)->update();

              $this->data['receipts'][$i]['payment_status'] = 100;
            }
          }
        }
      }
    }

    return $this->menuLayout('orders/detail', $this->data);
  }

  public function editForm() {
    $data = $this->request->getPost();
    if ( empty($data) ) return;
    $this->data['receipt'] = $this->receipt->where('receipt_id', $data["receipt_id"])->first();
    $this->data['shipments'] = $this->shipment->joinDelivery()->select('shipment.*, IFNULL(delivery.forward, 0) AS forword')->findAll();
    $this->data['details'] = $this->getOrderDetail($data['order_id'])->findAll();
    $this->data['order'] = $this->getOrder($data['order_id'])->first();
    // $this->data['deliveries'] = $this->delivery->select('CONVERT(SUM(delivery_price), FLOAT) AS delivery_price')->where('order_id', $data['order_id'])->groupBy('order_id')->findAll();
    $this->data['deliveries'] = $this->delivery->where('order_id', $data['order_id'])->findAll();

    return view('orders/includes/edit', $this->data);
  }

  public function pInvoice() {
    $data = $this->request->getPost();

    if ( empty($data['piControllType']) ) return redirect()->back();
    else {
      $data['type'] = $data['piControllType'];
      unset($data['piControllType']);
    } 
    
    if ( $data['type'] == 'cancel' ) {
      // 가격도 환불할건지 체크해서 가격은 유지할 경우, credit으로 등록
      if ( isset($data['payment_invoce_id']) && !empty($data['payment_invoce_id'])) {
        $paypalCancelResult = $this->PaypalController->cancelSentInvoice($data['payment_invoce_id']);

        if ( $paypalCancelResult['code'] != 204 ) {
          return redirect()->back()->with('error', 'paypal cancel error');
        }
      }

      $this->receipt->set('payment_status', -100)->where('receipt_id', $data['receipt_id'])->update();
      $this->order->set('order_check', -1)->where('id', $data['order_id'])->update();
    }

    if ( $data['type'] == 'edit' ) {
      $exceptCnt = 0;
      $shippingFee = 0;
      $minusOrderAmount = 0;
      print_r($data);
      if ( !empty($data['detail']) ) {
        foreach($data['detail'] AS $detail) {
          if ( $detail['order_excepted'] == 1 ) $exceptCnt++;
          if ( empty($detail['expiration_date']) ) unset($detail['expiration_date']);
          if ( $this->orderDetail->save($detail) ) {
            $detailAmounts = $this->orderDetail
                                ->select('CAST(SUM(prd_price * prd_order_qty) AS DOUBLE) AS amount')
                                ->select('CAST(SUM(prd_discount * prd_order_qty) AS DOUBLE) AS discount')
                                ->select('CAST((SUM(prd_price * prd_order_qty) - SUM(prd_discount * prd_order_qty)) AS DOUBLE) AS subtotal')
                                ->select('CAST(SUM(prd_price * prd_changed_qty) AS DOUBLE) AS difference')
                                ->where(['order_id'=> $data['order_id'], 'order_excepted' => 0])
                                ->first();

            if ( !empty($detailAmounts) ) {
              $data['order_amount'] = $detailAmounts['amount'];
              $data['discount_amount'] = $detailAmounts['discount'];
              $data['subtotal_amount'] = $detailAmounts['subtotal'];
            }
          }
        }
      }

      if ( !empty($data['product']) ) {
        foreach($data['product'] AS $product) {
          if ( !empty($product['idx']) && !empty($product['hs_code']) ) {
            $this->product->save($product);
          }
        }
      }

      if ( $exceptCnt > 0 ) {
        $data['order']['id'] = $data['order_id'];
        $data['order']['order_check'] = ( $exceptCnt > 0 ) ? 1 : 0 ;
        $data['order']['order_amount'] = $data['order_amount'];
        $data['order']['discount_amount'] = $data['discount_amount'];
        $data['order']['subtotal_amount'] = $data['subtotal_amount'];
        
        // echo "<Br/>order";
        // print_r($data['order']);
        // echo "<Br/>";
        $this->order->save($data['order']);
      }

      if ( !empty($data['delivery']) ) {
        foreach($data['delivery'] as $delivery) : 
          if ( isset($delivery['delivery_code'])) {
            if ($delivery['delivery_code'] == 'on') $delivery['delivery_code'] = 100;
          } else $delivery['delivery_code'] = 0;

          // if ( isset($delivery['forward'])) {
          //   if ( $delivery['forward'] == 'on' ) $delivery['forward'] = 1;
          // } else $delivery['forward'] = 0;

          $shippingFee += $delivery['delivery_price'];
          
          // if ( !$this->delivery->save($delivery)) {
          //   // print_r($this->delivery->errors());
          // } else {
          //   $data['receipt']['delivery_id'] = $delivery['id'];
          // }
        endforeach;
      }

      if ( !empty($data['receipt']) ) {
        // echo $data['subtotal_amount']." ".$detailAmounts['subtotal_amount'];
        if ( !empty($detailAmounts['subtotal_amount']) ) {
          if ( !empty($data['receipt']['rq_percent']) ) {
            $data['receipt']['rq_amount'] = floatval(sprintf('%0.2f', (($data['subtotal_amount'] - $data['paid']) * $data['receipt']['rq_percent'])));
            $data['receipt']['due_amount'] = floatval(sprintf('%0.2f', (($data['subtotal_amount'] - $data['paid']) - $data['receipt']['rq_amount'])));
          } else {
            $data['receipt']['rq_percent'] = -1;
            // $data['receipt']['due_amount'] = ($detailAmounts['subtotal_amount'] - $data['receipt']['rq_amount']);
          }
        }
        // echo "<br/>";
        // print_r($data['receipt']);
        // echo "<br/><br/>";
        
        if ($this->receipt->save($data['receipt'])) {
          if ( $data['receipt']['due_amount'] <= 0 ) {
            $this->order->set(['complete_payment' => 1])->where(['id' => $data['order_id']])->update();
          }

          if ( isset($data['receipt']['invoice_id']) && !empty($data['receipt']['invoice_id'])) {
            $paypalData = [ 'amount' => ($data['receipt']['rq_amount'] + $shippingFee)
                          , 'unit_amount' => $data['receipt']['rq_amount']
                          , 'currency_code' => $data['currency_code']];

            if ( !empty($data['delivery']) ) {
              $paypalData['shippingFee'] = $shippingFee;
            }

            $paypalResult = $this->PaypalController->fullyUpdateInvoice(
                                    $data['receipt']['invoice_id'],
                                    $paypalData
                            );
            // echo "<br/>";
            // print_r($paypalResult);
            // echo "<br/>";

            if ( $paypalResult['code'] != 200 ) {
              // return redirect()->back()->withInput()->with('error', 'paypal update error');
              echo 'paypal update error';
            }
          }
        }
      }
    }

    if ( $data['type'] == 'receipt' ) {
      if ( $data['payment_status'] != 100 ) return redirect()->back();
      $insertData = [ 'order_id' => $data['order_id']
                      , 'receipt_type'=> $data['receipt_type']
                      , 'due_amount' => 0
                      , 'rq_amount' => $data['request_amount']
                      , 'rq_percent' => 1
                    ];
      
      if ( isset($data['payment_invoce_id']) && !empty($data['payment_invoce_id']) ) {
        $invoiceDetail = $this->PaypalController->showInvoiceDetail($data['payment_invoce_id']);

        if ( !empty($invoiceDetail) ) {
          $data['primary_recipients'] = $invoiceDetail['data']['primary_recipients'];
          $data['buyerName'] = $data['buyer_name'];
          $data['unit_amount'] = $data['request_amount'];

          // $this->PaypalController->paypal($data);
          $this->PaypalController->makeInvoice($data);
          
          if ( !empty($this->PaypalController->result) ) {
            $newInvoice = $this->PaypalController->result;

            if ( $newInvoice['code'] == 200 || $newInvoice['code'] == 201 ) {
              $insertData['payment_invoice_id'] = $newInvoice['payment_invoice_id'];
              $insertData['payment_invoice_number'] = $newInvoice['payment_invoice_number'];
              $insertData['payment_url'] = $newInvoice['payment_url'];
            } else {
              return redirect()->back()->with('error', ($data['receipt_type'] + 1).'차 paypal invoice 발행 오류');
            }
          }
        } else return redirect()->back()->with('error', ($data['receipt_type'] + 1).'차 paypal invoice 발행 오류');
      }

      $this->receipt->save($insertData);
    }

    if ( $data['type'] == 'refund' ) {
      $receiptInfo = $this->receipt->where(['receipt_id' => $data["receipt_id"]])->first();
      
      if ( !empty($receiptInfo) ) :
        if ( $receiptInfo['payment_status'] != 100 ) :
          return redirect()->back()->with('error', '결제완료 일때만 가능함');
        endif;

        $data['payment_status'] = -200;
        $data['refund_date'] = date('Y-m-d');

        if ( isset($data['payment_invoce_id']) && !empty($data['payment_invoce_id']) ) :
          $paypalDetail = $this->PaypalController->showInvoiceDetail($data['payment_invoce_id']);

          if ( !empty($paypalDetail) && $paypalDetail['code'] == 200) {
            $refundData['method'] = $paypalDetail['data']['payments']['transactions'][0]['method'];
            $refundData['value'] = $paypalDetail['data']['payments']['paid_amount']['value'];
            $refundData['currency_code'] = $paypalDetail['data']['payments']['paid_amount']['currency_code'];
            $refundData['refund_date'] = $data['refund_date'];
            
            echo "<br/><Br/>";
            print_r($refundData);
            echo "<br/><Br/>";
            
            $this->PaypalController->recordRefundForInvoice($data['payment_invoce_id'], $refundData);
            // print_r($this->PaypalController->result);
            if ( $this->PaypalController->result['code'] == 200 ) :
              $receipt['payment_refund_id'] = $this->PaypalController->result['msg'];
            endif;
          }
        endif;

        $this->receipt->save($data);

        // if ( $this->receipt->save($data) ) :
          
        // endif;
      else :
        // 값이 없음.
      endif;  
    }

    if ( $data['type'] == 'delivery') {
      foreach( $data['delivery'] as $delivery ) : 
        if ( isset($delivery['delivery_code'])) {
          if ($delivery['delivery_code'] == 'on') $delivery['delivery_code'] = 100;
        } else {
          if ( !empty($delivery['receipt_id']) ) {
            $delivery['delivery_code'] = 100;
          } else $delivery['delivery_code'] = 0;
        }

        // if ( isset($delivery['forward'])) {
        //   if ( $delivery['forward'] == 'on' ) $delivery['forward'] = 1;
        // } else $delivery['forward'] = 0;

      if ( $this->delivery->save($delivery)) {
        $receiptSave = ["receipt_id" => $delivery['receipt_id']
                      , "delivery_id" => $delivery['id']];

        if ( $this->receipt->save($receiptSave) ) {
          $receipt = $this->receipt->where('receipt_id', $delivery['receipt_id'])->first();
          
          if ( strtolower($delivery['payment']) == 'paypal' ) {
            if ( !empty($receipt['payment_invoice_id']) ) {
              $paypalData = [ 'amount' => ($receipt['rq_amount'] + $delivery['delivery_price'])
                            , 'unit_amount' => $receipt['rq_amount']
                            , 'currency_code' => $delivery['currency_code']];

              if ( !empty($data['delivery']) ) {
                $paypalData['shippingFee'] = $delivery['delivery_price'];
              }

              $paypalResult = $this->PaypalController->fullyUpdateInvoice(
                                      $receipt['payment_invoice_id'],
                                      $paypalData);
              if ( $paypalResult['code'] != 200 ) {
                session()->setFlashdata('error', 'paypal update error');
              }
            }
          }
        }
      }
      endforeach;
    }
    // echo "<br><br>";
    // print_r($data);
    return redirect()->back();
  }

  public function inventoryDetail() { // 재고요청상태일때
    if ( !in_array('/orders/inventory.js', $this->data['header']['js']) ) {
      array_push($this->data['header']['js'], '/orders/inventory.js');
    }
    $orderId = $this->request->uri->getSegment(3);
    
    $packagingStatus = $this->packaging
                            ->packaging(['where' => ['packaging.order_id' => $orderId
                                                    , 'packaging_detail.in_progress' => 1
                                                    , 'packaging_detail.complete' => 0]
                                        , 'orderBy' => 'packaging_status.order_by DESC'])
                            ->first();
    if ( !empty($packagingStatus) ) {
      if ( $packagingStatus['order_by'] == 1 ) {
        $nextPackagingStatus = $this->packagingStatus->where('order_by', ($packagingStatus['order_by'] + 1))->first();
        if ( $this->packagingDetail->save(['packaging_id' => $packagingStatus['packaging_id']
                                          , 'status_id' => $nextPackagingStatus['idx'] ])) {
          if ( !$this->packagingDetail->save(['idx' => $packagingStatus['detail_idx'], 'complete' => 1]) ) {
            return redirect()->to(site_url(previous_url()))->with('error', '오류');
          } else {
            // $this->data['nextPackaging'] = $this->packagingStatus->where('order_by', ($nextPackagingStatus['order_by'] + 1))->first();
          }
        } else {
          return redirect()->to(site_url(previous_url()))->with('error', '오류');
        }
      } else if ( $packagingStatus['order_by'] == 2 ) { 
        $nextPackagingStatus = $this->packagingStatus->where('order_by', ($packagingStatus['order_by'] + 1))->first();
        $this->data['nextPackaging'] = array_merge($nextPackagingStatus, ['packaging_id' => $packagingStatus['packaging_id']]);
      }
    }
    
    $this->data['order'] = $this->getOrder($orderId)->first();
    if ( empty($orderId) || empty($this->data['order']) ) return redirect()->to(site_url('order'));
  
    $this->data['details'] = $this->getOrderDetail($orderId)->findAll();

    if ( !empty($this->data['details']) ) :
      $this->data['requirement'] = [];
      foreach($this->data['details'] AS $detail ) :
        array_push($this->data['requirement']
                  , $this->requirementRequest->requirement(['where' => ['requirement_request.order_id'=> $orderId
                                                                        , 'requirement_request.order_detail_id' => $detail['id']]])
                                              ->findAll());
      endforeach;
    endif;

    return $this->menuLayout('orders/inventoryCheckDetail', $this->data);
  }

  public function inventoryEdit() {
    $details = $this->request->getPost('detail');
    $requirement = $this->request->getPost('requirement');
    $order = $this->request->getPost('order');
    $packaging = $this->request->getPost('packaging');

    print_r($details);
    echo "<Br/><Br/>";
    print_r($requirement);
    echo "<Br/><Br/>";
    print_r($order);
    echo "<Br/><Br/>";
    print_r($packaging);
    echo "<Br/><Br/>";

    // if ( site_url(previous_url()) != site_url(uri_string()) && !empty($params) ) {
    if ( !empty($details) ) {
      foreach( $details AS $detail ) :
        if ( !empty($detail) ) {
          print_r($detail);
          echo "<br/>";
          unset($detail['request_amount']);
          if ( !empty($detail['id']) && isset($detail['id']) )  {
            $detailID = $detail['id'];
            unset($detail['id']);
          }

          if ( empty($detail['order_excepted_check']) ) {
            unset($detail['order_excepted_check']);
            unset($detail['order_excepted']);
          } else {
            if ( empty($detail['order_excepted']) ) {
              $detail['order_excepted'] = 0;
            } else {
              unset($detail['prd_price_changed']);
              unset($detail['prd_qty_changed']);
            }
          }

          if ( empty($detail['prd_price_changed']) ) {
            unset($detail['prd_price_changed']);
            unset($detail['prd_change_price']);
          }

          if ( empty($detail['prd_qty_changed']) ) {
            unset($detail['prd_qty_changed']);
            unset($detail['prd_change_qty']);
          }

          print_r($detail);
          echo "<Br/><br/>";
          // if ( !empty($detail) ) {
          //   $detail['changed_manager'] = session()->userData['idx'];
          //   $detail['id'] = $detailID;

          //   $this->orderDetail->save($detail);
          // }
        }
      endforeach;
    // } else {
    //   return redirect()->to(site_url(previous_url()))->with('error', 'input date error');
    }

    if ( !empty($requirement) ) {
      foreach($requirement AS $require) :
        $this->requirementRequest->save($require);
      endforeach;
    }

    if ( !empty($order) ) {
      // if ( !empty($packaging) ) {      
        if ( array_key_exists('id', $order) ) {
          if ( $order['request_amount'] != $order['inventory_fixed_amount'] ) {
              $this->order->save($order);
          }
        }
      // }
    }
  }

  public function getOrders() {
    return $this->order->orderJoin()
                      ->select('packaging_status.payment_check')
                      ->select('packaging_status.status_name')
                      ->select('receipt_group.payment_status_group')
                      ->select('CAST(prd_weight.shipping_weight AS DOUBLE) AS shipping_weight')
                      ->select('CAST(delivery.delivery_price AS DOUBLE) AS delivery_price')
                      ->join('packaging', 'packaging.order_id = orders.id')
                      ->join('packaging_detail', 'packaging_detail.packaging_id = packaging.idx AND packaging_detail.in_progress = 1 AND packaging_detail.complete = 0')
                      ->join('packaging_status', 'packaging_status.idx = packaging_detail.status_id')
                      ->join('( SELECT orders_detail.order_id, SUM(product.shipping_weight) AS shipping_weight
                                FROM product
                                  JOIN orders_detail ON orders_detail.prd_id = product.id
                                GROUP BY orders_detail.order_id) AS prd_weight'
                              , 'prd_weight.order_id = orders.id', 'RIGHT')
                      ->join("( SELECT order_id, SUM(delivery_price) AS delivery_price
                                FROM delivery 
                                GROUP BY delivery.order_id ) AS delivery"
                              , 'delivery.order_id = orders.id', 'RIGHT')
                      ->join('( SELECT order_id, GROUP_CONCAT(receipt_type, ":", payment_status order by receipt_id) AS payment_status_group 
                               FROM orders_receipt GROUP BY order_id ORDER BY receipt_id) AS receipt_group', 'receipt_group.order_id = orders.id', 'left outer')
                      ->where('orders.available', 1);
  }

  public function getOrder($orderId = null) {
    if ( !empty($orderId) ) {
      $this->order->join("( SELECT order_id, SUM(rq_amount) AS amount_paid 
                        FROM orders_receipt 
                        WHERE order_id = {$orderId} AND payment_status = 100
                      ) AS amount_paid", 'amount_paid.order_id = orders.id', 'left outer')
                  ->where('orders.id', $orderId);
    }

    return $this->order->orderJoin()
                ->select('buyers.name AS buyer_name')
                ->select('users.idx AS user_idx, users.id AS user_id, users.name AS user_name, users.email AS user_email')
                ->select('manager.name AS manager_name, manager.email AS manager_email')
                ->select('buyers_address.consignee, buyers_address.region,
                          buyers_address.streetAddr1, buyers_address.streetAddr2,
                          buyers_address.zipcode, buyers_address.phone_code AS phonecode, buyers_address.phone')
                ->select('CAST(IFNULL(amount_paid.amount_paid, 0) AS DOUBLE) AS amount_paid')
                ->select('SUM(CAST(IFNULL(delivery.delivery_price, 0) AS DOUBLE)) AS delivery_price')
                ->select('orders_receipt.receipt_type, orders_receipt.payment_status')
                ->join('buyers', 'buyers.id = orders.buyer_id')
                ->join('users', 'users.buyer_id = buyers.id')
                ->join('manager', 'manager.idx = buyers.manager_id')
                ->join('delivery', 'delivery.order_id = orders.id AND delivery.delivery_code = 100', 'left outer')
                ->join('buyers_address', 'buyers_address.idx = orders.address_id')
                // ->join("( SELECT order_id, SUM((prd_price * prd_changed_qty))")
                ->join("orders_receipt", "orders_receipt.order_id = orders.id", "left outer");
  }

  public function getOrderDetail($orderId = null) {
    return $this->data['details'] = $this->orderDetail
                                        ->select('currency.currency_sign AS currency_sign')
                                        ->select('currency.currency_float AS currency_float')
                                        ->productBrandJoin()
                                        ->join('orders', 'orders.id = orders_detail.order_id', 'RIGHT')
                                        ->join("currency_rate", "currency_rate.cRate_idx = orders.currency_rate_idx", "LEFT OUTER")
                                        ->join("currency", "currency.idx = currency_rate.currency_idx", "LEFT OUTER")
                                ->where('orders_detail.order_id', $orderId);
  }


  public function paypalList() {  // 임시로 페이팔 invoice. b2b 오픈전까지 사용
    $params = [];
    $query = [];

    if ( $this->request->getPost() ) {
      $params = $this->request->getPost();
      foreach ($params AS $i => $c ) {
        if ( gettype($c) != 'string' ) {
        } else {
          if ( !empty($c) ) {
            if ( $i == 'status') $params[$i] = [$c];
            else $params[$i] = $c;
          } else unset($params[$i]);
        }
      }
      
      if ( !empty($params) ) $params = json_encode($params);
    } 

    $list = $this->PaypalController->searchInvoices($params, $query);

    $this->data['invoiceStatus'] = $this->PaypalConfig->invoiceStatus;
    $this->data['invoiceViewrUrl'] = $this->PaypalConfig->invoiceViewer."#";
    $this->data['managers'] = $this->managerModel->where('active', 1)->findAll();
    $this->data['paypalList'] = !empty($list) ? $list['items'] : [];
   
    // session()->setFlashdata('params', $this->request->getPost());
   
    return $this->menuLayout('paypal/main', $this->data);
  }

  public function paypal() {  // 임시로 페이팔 invoice. b2b 오픈전까지 사용
    $data = $this->request->getVar();
    $amount = 0;
    if ( isset( $data['partial_payment'] ) ) {
      $data['partial_payment'] = TRUE;
    } else $data['partial_payment'] = FALSE;

    if ( !empty($data['items']) ) {
      foreach ( $data['items'] AS $item ) {
        $amount += $item['unit_amount'];
      }
    }
    // $this->PaypalController->paypal($data);

    $this->PaypalController->makeInvoice($data);
    print_r($this->PaypalController->result);

    if ( $this->PaypalController->result['code'] == 200 ||
        $this->PaypalController->result['code'] == 202 ) {
      // $data['manager_id'] = session()->userData['idx'];
      // $data['paypal_id'] = $this->PaypalController->result['payment_invoice_id'];
      // $data['invoice_url'] = $this->PaypalController->result['payment_url'];
      // // $data['invoice_number'] = $this->PaypalController->result['payment_invoice_number'];
      // // $data['buyer_email'] = $data['billing_info']['email_address'];
      // // // $data['invoice_status'] = '';
      // // $data['amount'] = $amount;
      // // $data['due_amount'] = $amount;
      // // $data['invoice_status'] = 'SENT';
      // // $data['sandbox'] = $this->PaypalConfig->sandbox;

      // // if ( !$this->paypalModel->insert($data) ) {
      // //   echo 'error';
      // // } else 
      return redirect()->back();
    }
    // if ( $this->PaypalController->makeInvoice($data) ) {

    // }
  }
}