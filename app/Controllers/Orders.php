<?php
namespace App\Controllers;

use App\Models\CurrencyModel;
use App\Models\OrdersModel;
use App\Models\OrderDetailModel;
use App\Models\OrderStatusModel;
use App\Models\RequirementRequestModel;
use App\Models\RequirementOptionModel;
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
    $this->requirementOption = new RequirementOptionModel();
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

    $this->data['header'] = ['css' => ['/orders/orders.css', '/orders/invoiceDelivery.css'
                                      , '/table.css', '/inputLabel.css']
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
        $this->order->where('packaging_status.idx', $params['order_status']);
      }
      if ( !empty($params['start_date']) ) {
        $this->order->where('DATE(orders.created_at) >=', $params['start_date'] );
      }
      if ( !empty($params['end_date']) ) {
        $this->order->where('DATE(orders.created_at) <=', $params['end_date'] );
      }
    }

    $this->data['orderStatus'] = $this->packagingStatus->where('available', 1)->orderBy('order_by ASC')->findAll();
    $this->data['orders'] = $this->getOrders()
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
    $this->data['receipts'] = $this->receipt
                                ->select('orders_receipt.*, delivery.delivery_price')
                                ->join('delivery', 'delivery.id = orders_receipt.delivery_id', 'left outer')
                                ->where('orders_receipt.order_id', $orderId)->findAll();
    $this->data['shipments'] = $this->shipment->findAll();
    // $this->data['deliveries'] = $this->delivery
    //                                 ->select('delivery.*, IFNULL(orders_receipt.delivery_id, 0) AS receipt_included')
    //                                 ->join('orders_receipt', 'orders_receipt.delivery_id = delivery.id', 'left outer')
    //                                 ->where('delivery.order_id', $orderId)
    //                                 ->findAll();
    $this->data['deliveries'] = $this->delivery->where('delivery.order_id', $orderId)->findAll();
    $this->data['packaging'] = $this->packaging
                                    ->packaging(['where' => ['packaging.order_id'=> $orderId]
                                                , 'orderBy' => 'packaging_status.order_by DESC'])
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
                                      ->first();

    $this->data['packagingStatus'] = $this->packagingStatus
                                          ->packagingStatus($orderId)
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
            
            if ( $this->PaypalController->result['code'] != 404 ) {
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
          } else {
            if ( !empty($this->data['order']) ) {
              
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
    $this->data['currency'] = $this->currency->where('available', 1)->find();
    // $this->data['deliveries'] = $this->delivery->select('CONVERT(SUM(delivery_price), FLOAT) AS delivery_price')->where('order_id', $data['order_id'])->groupBy('order_id')->findAll();
    $this->data['deliveries'] = $this->delivery->where('order_id', $data['order_id'])->findAll();

    return view('orders/includes/edit', $this->data);
  }

  public function pInvoice() {
    $data = $this->request->getPost();
    // var_dump($data);
    // return;
    if ( empty($data['piControllType']) ) return redirect()->back();
    else {
      $data['type'] = $data['piControllType'];
      unset($data['piControllType']);
    } 
    
    if ( $data['type'] == 'cancel' ) {
      // 가격도 환불할건지 체크해서 가격은 유지할 경우, credit으로 등록
      if ( isset($data['receipt']['payment_invoce_id']) && !empty($data['receipt']['payment_invoce_id'])) {
        $paypalCancelResult = $this->PaypalController->cancelSentInvoice($data['receipt']['payment_invoce_id']);

        if ( $paypalCancelResult['code'] != 204 ) {
          return redirect()->back()->with('error', 'paypal cancel error');
        }
      }
      $this->receipt->set('payment_status', -100)->where('receipt_id', $data['receipt']['receipt_id'])->update();
      $this->order->set('order_check', -1)->where('id', $data['order_id'])->update();
    }

    if ( $data['type'] == 'edit' ) {
      if ( !empty($data['receipt']) ) {
        if ( isset($data['receipt']['payment_status']) ) {
          $data['receipt']['payment_status'] = 100;
          if ( empty($data['receipt']['payment_date']) ) {
            $data['receipt']['payment_date'] = date('Y-m-d');
          }
        } else {
          $data['receipt']['payment_date'] = NULL;
          $data['receipt']['payment_status'] = 0;
        }

        if ( !empty($data['order_id']) ) {
          $getOrder = $this->order->where(['id'=> $data['order_id'], 'available' => 1])->first();
          if ( !empty($getOrder) ) {
            if ( !$getOrder['complete_payment'] ) {
              if ( $getOrder['order_amount'] != $data['order_amount'] ) return redirect()->back()->with('error', 'order amount error')->withInput();
            } else {
              return redirect()->back()->with('error', '이미 결제 완료된 주문입니다. 재확인 해주세요');
            }
          }
        }

        $receiptModification = false;
        $tempRequestPecent = null;
        if ( !empty($data['receipt']['receipt_id']) ) {
          $getReceipt = $this->receipt->where(['receipt_id' => $data['receipt']['receipt_id']])->first();
          if ( !empty($getReceipt) ) {
            $tempRequestPecent = $getReceipt['rq_percent'];

            if ( !empty($data['receipt']['rq_percent']) ) {
              if ( $getReceipt['rq_percent'] != $data['receipt']['rq_percent'] ) {
                $receiptModification = true;
                $tempRequestPecent = $data['receipt']['rq_percent'];
                $data['receipt']['rq_amount'] = floatval(sprintf('%0.2f', (($data['order_amount'] - $data['amount_paid']) * $data['receipt']['rq_percent'])));
                $data['receipt']['due_amount'] = floatval(sprintf('%0.2f', (($data['order_amount'] - $data['amount_paid']) - $data['receipt']['rq_amount'])));
              }            
            } else {
              $receiptModification = true;
              // $data['receipt']['rq_percent'] = -1;
              $tempRequestPecent = -1;
            }
          }
        }
        var_dump($data);
        return;

        if ( floatval(sprintf('%0.2f', $data['receipt']['rq_amount'] + $data['receipt']['due_amount'] + $data['amount_paid'])) != $data['order_amount'] ) {
          return redirect()->back()->with('error', 'PI 가격 계산 중 오류 발생')->withInput();
        }
        
        // delivery
        if ( !empty($data['delivery']) && !empty($data['order_id']) ) {
          $data['delivery']['order_id'] = $data['order_id'];
  
          if ( isset($data['delivery']['forward'])) {
            if ( $data['delivery']['forward'] == 'on' ) $data['delivery']['forward'] = 1;
          } else $data['delivery']['forward'] = 0;
  
  
          if ( !$this->delivery->save($data['delivery']) ) {
            return redirect()->back()->with('error', $this->delivery->errors());
          } else {
            $data['receipt']['delivery_id'] = $this->delivery->getInsertID();
          }
        }
        // delivery

        if ( !$this->receipt->save($data['receipt']) ) {
          return redirect()->back()->with('error', '영수중 수정중 오류 발생');
        } else {
          $shippingFee = 0;
          
          if ( !empty($data['delivery']) && isset($data['delivery']['delivery_price'])) {
            $receiptModification = true;
            $shippingFee = $data['delivery']['delivery_price'];
          }

          if ( isset($data['receipt']['payment_invoce_id']) && !empty($data['receipt']['payment_invoce_id'])) {
            if ( $receiptModification ) {
              $paypalData = ['amount' => ($data['receipt']['rq_amount'] + $shippingFee)
                            , 'unit_amount' => $data['receipt']['rq_amount']
                            , 'currency_code' => $data['currency_code']
                            , 'shippingFee' => $shippingFee];

              $paypalResult = $this->PaypalController->fullyUpdateInvoice($data['receipt']['payment_invoce_id'], $paypalData);

              if ( $paypalResult['code'] != 200 ) {
                $data['receipt']['rq_percent'] = $tempRequestPecent;
                $data['receipt']['rq_amount'] = floatval(sprintf('%0.2f', (($data['order_amount'] - $data['amount_paid']) * $data['receipt']['rq_percent'])));
                $data['receipt']['due_amount'] = floatval(sprintf('%0.2f', (($data['order_amount'] - $data['amount_paid']) - $data['receipt']['rq_amount'])));

                $this->receipt->save($data['receipt']);
                return redirect()->back()->with('error', 'paypal invoice 수정오류');
              }
            }
          }
        }
      }
    }

    if ( $data['type'] == 'receipt' ) {
      $isPaypal = false;
      $invoice_data = NULL;
      $getOrder = Array();
      
      if ( !empty($data['order_id']) ) {
        $getOrder = $this->order->where(['id' => $data['order_id'], 'available' => 1])->first();
        if ( !empty($getOrder) ) {
          if ( !empty($getOrder['order_amount']) ) {
            $tempTotal = floatval(sprintf('%0.2f', ($data['amount_paid'] + $data['request_amount'])));
            
            if ( $tempTotal > $getOrder['order_amount'] && $tempTotal < $getOrder['order_amount'] ) {
              return redirect()->back()->with('error', '주문금액 재확인 요청');
            }
            if ( $getOrder['payment_id'] == 1 ) $isPaypal = true;
          } else return redirect()->back()->with('error', '주문처리가 완료되기 전 주문인거 같습니다.');
        } else {
          return redirect()->back()->with('error', '해당하는 주문 정보가 없습니다.');
        }
      }

      $receiptCheck = $this->receipt->where(['order_id' => $data['order_id'], 'receipt_id' => $data['receipt']['receipt_id']])->first();
      if ( !empty($receiptCheck) ) {
        if ( $data['receipt_type'] != $receiptCheck['receipt_type'] ) return redirect()->back()->with('error', '영수증 정보가 일치하지 않습니다.');
        if ( $data['amount_paid'] >= $data['order_amount'] ) return redirect()->back()->with('error', '영수증 발부하고자 하는 결제금액이 잘못되었습니다.');
        if ( $receiptCheck['payment_status'] == 100 ) { 
          if ( $receiptCheck['due_amount'] > 0 ) {
            $reqeust_amount = floatval(sprintf('%0.2f', $data['request_amount']));

            $nextReceiptCheck = $this->receipt->where(['order_id' => $data['order_id'], 'receipt_type' => ($receiptCheck['receipt_type'] + 1)])->first();
            if ( !empty($nextReceiptCheck) ) {
              if ( $nextReceiptCheck['payment_status'] != -1 ) return redirect()->back()->with('error', '이미 있다');
              else {
                if ( !$isPaypal ) {
                  return redirect()->back()->with('error', 'paypal 아니다');
                }
              }
            } else {
              $nextReceiptData = [
                'order_id'      => $data['order_id'],
                'receipt_type'  => ($data['receipt_type'] + 1),
                // 'due_amount'    => $receipt['due_amount'] = floatval(sprintf('%0.2f', ($data['order_amount'] - ($data['amount_paid'] + $data['request_amount'])))),
                'due_amount'    => 0,
                'rq_amount'     => $reqeust_amount,
                'rq_percent'    => 1,
              ];

              if ( $this->receipt->save($nextReceiptData) ) {
                $receiptId = $this->receipt->getInsertID();
              }
            }

            if ( isset($receiptId) && !empty($receiptId) ) {
              if ( $isPaypal ) {
                if ( !empty($receiptCheck['payment_invoice_id']) ) {
                  $getInvoice = $this->PaypalController->showInvoiceDetail($receiptCheck['payment_invoice_id']);
                  if ( !empty($getInvoice) ) {
                    if ( $getInvoice['code'] >= 200 && $getInvoice['code'] <= 201 ) {
                      if ( $getInvoice['data']['status'] == 'MARKED_AS_PAID' || $getInvoice['data']['status'] == 'PAID') {
                        $getInvoiceData = [
                          'currency_code' => $getInvoice['data']['detail']['currency_code'],
                          'invoice_number' => $getInvoice['data']['detail']['invoice_number']."_".$data['receipt_type'],
                          'buyerName' => $getInvoice['data']['primary_recipients'][0]['billing_info']['name']['given_name'],
                          'email' => $getInvoice['data']['primary_recipients'][0]['billing_info']['email_address'],
                          'phone_code' => $getInvoice['data']['primary_recipients'][0]['billing_info']['phones'][0]['country_code'],
                          'phone' => $getInvoice['data']['primary_recipients'][0]['billing_info']['phones'][0]['national_number'],
                          'consignee' => $getInvoice['data']['primary_recipients'][0]['shipping_info']['name']['given_name'],
                          'streetAddr1' => $getInvoice['data']['primary_recipients'][0]['shipping_info']['address']['address_line_1'],
                          'streetAddr2' => $getInvoice['data']['primary_recipients'][0]['shipping_info']['address']['address_line_2'],
                          'zipcode' => $getInvoice['data']['primary_recipients'][0]['shipping_info']['address']['postal_code'],
                          'country_code' => $getInvoice['data']['primary_recipients'][0]['shipping_info']['address']['country_code'],
                          'subtotal' => $reqeust_amount,
                        ];

                        // $this->PaypalController->paypal()
                      } else {
                        if ( !$this->receipt->save(['receipt_id' => $receiptCheck['receipt_id'], 'payment_status' => 0]) ) {
                          return redirect()->back()->with('error', '결제완료가 되지 않은 영수증입니다.');
                        }
                      }
                    } elseif ( $getInvoice['code'] == 404 ) {
                      // session()->setFlashdata('error', '없는 invoice');
                      return redirect()->back()->with('error', '없는 invoice');
                    } else {
                      return redirect()->back()->with('error', '유효하지 않는 paypal invoice 입니다.');
                    }
                  } else {
                    return redirect()->back()->with('error', '페이팔 오류');
                  }
                } else {
                  return redirect()->back()->with('error', '유효하지 않는 정보입니다.');
                }
              } else {
                if ( !$this->receipt->save(['receipt_id' => $receiptId, 'payment_status' => 0]) ) {
                  return redirect()->back()->with('error', '결제상태 처리중 오류 발생');
                }
              }
            }            
          } else {
            return redirect()->back()->with('error', '결제 잔액이 0원입니다. 더이상의 영수증 발급이 불가능합니다.');
          }
        }
        if ( $receiptCheck['payment_status'] == -1 ) { 
          return redirect()->back()->with('error', '오류가 있는 영수증입니다. '.($data['receipt_type'] + 1).'차 영수증 발급을 위해서는 '.$data['receipt_type'].'차 영수증을 다시 확인해주세요.');
        }
        if ( $receiptCheck['payment_status'] == 0 ) {
          return redirect()->back()->with('error', '이전 결제 처리가 완료되지 않았습니다.');
        }
      }
    }

    if ( $data['type'] == 'refund' ) {
      $receiptInfo = $this->receipt->where(['receipt_id' => $data['receipt']["receipt_id"]])->first();
      
      if ( !empty($receiptInfo) ) :
        if ( $receiptInfo['payment_status'] != 100 ) :
          return redirect()->back()->with('error', '결제완료 일때만 가능함');
        endif;

        $data['payment_status'] = -200;
        $data['refund_date'] = date('Y-m-d');

        if ( isset($data['receipt']['payment_invoce_id']) && !empty($data['receipt']['payment_invoce_id']) ) :
          $paypalDetail = $this->PaypalController->showInvoiceDetail($data['receipt']['payment_invoce_id']);

          if ( !empty($paypalDetail) && $paypalDetail['code'] == 200) {
            $refundData['method'] = $paypalDetail['data']['payments']['transactions'][0]['method'];
            $refundData['value'] = $paypalDetail['data']['payments']['paid_amount']['value'];
            $refundData['currency_code'] = $paypalDetail['data']['payments']['paid_amount']['currency_code'];
            $refundData['refund_date'] = $data['refund_date'];
            
            echo "<br/><Br/>";
            print_r($refundData);
            echo "<br/><Br/>";
            
            $this->PaypalController->recordRefundForInvoice($data['receipt']['payment_invoce_id'], $refundData);
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

    // if ( $data['type'] == 'delivery') {
    //   foreach( $data['delivery'] as $delivery ) : 
    //     if ( isset($delivery['delivery_code'])) {
    //       if ($delivery['delivery_code'] == 'on') $delivery['delivery_code'] = 100;
    //     } else {
    //       if ( !empty($delivery['receipt_id']) ) {
    //         $delivery['delivery_code'] = 100;
    //       } else $delivery['delivery_code'] = 0;
    //     }

    //     // if ( isset($delivery['forward'])) {
    //     //   if ( $delivery['forward'] == 'on' ) $delivery['forward'] = 1;
    //     // } else $delivery['forward'] = 0;

    //   if ( $this->delivery->save($delivery)) {
    //     $receiptSave = ["receipt_id" => $delivery['receipt_id']
    //                   , "delivery_id" => $delivery['id']];

    //     if ( $this->receipt->save($receiptSave) ) {
    //       $receipt = $this->receipt->where('receipt_id', $delivery['receipt_id'])->first();
          
    //       if ( strtolower($delivery['payment']) == 'paypal' ) {
    //         if ( !empty($receipt['payment_invoice_id']) ) {
    //           $paypalData = [ 'amount' => ($receipt['rq_amount'] + $delivery['delivery_price'])
    //                         , 'unit_amount' => $receipt['rq_amount']
    //                         , 'currency_code' => $delivery['currency_code']];

    //           if ( !empty($data['delivery']) ) {
    //             $paypalData['shippingFee'] = $delivery['delivery_price'];
    //           }

    //           $paypalResult = $this->PaypalController->fullyUpdateInvoice(
    //                                   $receipt['payment_invoice_id'],
    //                                   $paypalData);
    //           if ( $paypalResult['code'] != 200 ) {
    //             session()->setFlashdata('error', 'paypal update error');
    //           }
    //         }
    //       }
    //     }
    //   }
    //   endforeach;
    // }
    return redirect()->back();
  }

  public function inventoryDetail() { // 재고요청상태일때
    if ( !in_array('/orders/inventory.js', $this->data['header']['js']) ) {
      array_push($this->data['header']['js'], '/orders/inventory.js');
    }
    $orderId = $this->request->uri->getSegment(3);
    if ( empty($orderId) ) return redirect()->back()->with('error', '일치하는 order 정보가 없습니다.');
   
    $packagingDetail = $this->packaging
                            ->packaging(['where' => ['packaging.order_id' => $orderId
                                                    , 'packaging_detail.in_progress' => 1
                                                    , 'packaging_detail.complete' => 0
                                                    , 'packaging_status.available' => 1]
                                        , 'orderBy' => 'packaging_status.order_by DESC'])
                            ->first();

    if ( !empty($packagingDetail) ) {
      $this->data['packaging_id'] = $packagingDetail['packaging_id'];
      $this->data['price_disabled'] = $packagingDetail['requirement_option_check']; // check 필요
      $this->data['option_disabled'] = $packagingDetail['requirement_option_disabled']; // check 필요
      $this->data['packagingStatus'] = $this->getCurrentStepPackageStatus($packagingDetail);
    }
    
    $this->data['order'] = $this->getOrder($orderId)->first();
    if ( empty($orderId) || empty($this->data['order']) ) return redirect()->to(site_url('order'));
  
    $this->data['details'] = $this->getOrderDetail($orderId)->findAll();
    
    // $this->data['receipts'] = $this->receipt->select('orders_receipt.*, delivery.delivery_price')->join('delivery', 'delivery.id = orders_receipt.delivery_id', 'left outer')->where('orders_receipt.order_id', $orderId)->findAll();
    $this->data['receipts'] = $this->receipt
                                  ->select('orders_receipt.*')
                                  ->select('delivery.id AS delivery_id, delivery.forward
                                          , delivery.shipment_id, delivery.payment_id
                                          , delivery.packaging_id, delivery.ci_number
                                          , delivery.delivery_currency_idx, delivery.delivery_price
                                          , delivery.delivery_status, delivery.delivery_code')
                                  ->join('delivery', 'delivery.id = orders_receipt.delivery_id', 'left outer')
                                  ->where('orders_receipt.order_id', $orderId)->findAll();
    // $this->data['deliveries'] = $this->delivery->where('delivery.order_id', $orderId)->findAll();
    $this->data['shipments'] = $this->shipment->findAll();
    $this->data['currency'] = $this->currency->where('available', 1)->find();
    $this->data['requirementOption'] = $this->requirementOption->where('available', 1)->orderBy('sort ASC')->findAll();
    
    if ( !empty($this->data['details']) ) :
      foreach($this->data['details'] AS $dIdx => $detail ) :
        $requireRequest = $this->requirementRequest
                                ->requirement(['where' => ['requirement_request.order_id'=> $orderId
                                              , 'requirement_request.order_detail_id' => $detail['id']]])
                                ->findAll();
        if ( !empty($requireRequest) ) {
          $this->data['details'][$dIdx]['requirement'] = $requireRequest;
          foreach ( $requireRequest AS $req ) {
            if ( !empty($req['requirement_selected_option_id']) ) {
              $checkOpts = explode(',', $req['requirement_selected_option_id']);

              if ( !empty($checkOpts) ) {
                if ( !empty($this->data['requirementOption']) ) {
                  foreach($this->data['requirementOption'] AS $opts ) {
                    foreach( $checkOpts AS $cOpts ) {
                      if ( $cOpts == $opts['idx'] ) {
                        if ( $opts['order_condition'] == -1 ) {
                          $this->data['details'][$dIdx]['cancele_request'] = TRUE;
                          break;
                        }
                      }
                    }
                  }
                }
              }
            }
         }
        }
      endforeach;
    endif;

    if ( !empty($this->data['receipts']) ) {
      foreach($this->data['receipts'] as $i => $receipt) { 
        if ( $receipt['payment_status'] == 0 ) {
          if ( !is_null($receipt['payment_invoice_id']) ) {
            $updateData = [];
            $paypalDetail = $this->PaypalController->showInvoiceDetail($receipt['payment_invoice_id']);
            
            // var_dump($this->PaypalController->result);
            if ( $this->PaypalController->result['code'] != 404 ) {
              if ( $paypalDetail['data']['due_amount']['value'] == 0 && ($paypalDetail['data']['status'] == 'PAID' || $paypalDetail['data']['status'] == 'MARKED_AS_PAID') ) {
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
    }
    // if ( !empty($this->data['packagingStatus']) && $this->data['packagingStatus'][1]['department_ids'] == false ) {
    //   return redirect()->to(site_url("orders/detail/{$orderId}"));
    // } else return $this->menuLayout('orders/inventoryCheckDetail', $this->data);
    return $this->menuLayout('orders/inventoryCheckDetail', $this->data);
  }

  public function inventoryEdit() {
    $details = $this->request->getPost('detail');
    $requirement = $this->request->getPost('requirement');
    $order = $this->request->getPost('order');
    $packaging = $this->request->getPost('packaging');
  
    // var_dump($this->request->getPost());
    var_dump($details);
    var_dump($requirement);
    var_dump($order);
    var_dump($packaging);
    return;

    // if ( site_url(previous_url()) != site_url(uri_string()) && !empty($params) ) {
    if ( !empty($details) ) {
      foreach( $details AS $detail ) :
        if ( !empty($detail) ) {
          unset($detail['request_amount']);
          if ( !empty($detail['id']) && isset($detail['id']) )  {
            $detailID = $detail['id'];
          } else { 
            return redirect()->back()->with('error', '해당하는 주문 정보를 제대로 못 읽어왔음. 다시 시도');
          }
          
          if ( !isset($detail['order_excepted']) ) {
            $detail['order_excepted'] = 0;
          }

          if ( !array_key_exists('id', $detail) ) $detail['id'] = $detailID;
          $detail['changed_manager'] = session()->userData['idx'];          
          if ( !$this->orderDetail->save($detail) ) {
            return redirect()->to(site_url(previous_url()))->with('error', "{$detail['id']}에 해당하는 주문정보 수정 중 오류 발생.");
          }
        }
        endforeach;
    } else {
      return redirect()->to(site_url(previous_url()))->with('error', 'input date error');
    }

    if ( !empty($requirement) ) {
      foreach($requirement AS $require) :
        if ( !empty($require) ) {
          foreach($require AS $requireDetail ) {
            if ( !empty($requireDetail['requirement_option_ids']) || !empty($requireDetail['requirement_reply']) ) {
              $this->requirementRequest->save($requireDetail);
            }
          }
        }
      endforeach;
    }

    if ( !empty($order) ) {
      if ( array_key_exists('id', $order) ) {
        if ( empty($order['inventory_fixed_amount']) ) unset($order['inventory_fixed_amount']);
        if ( empty($order['fixed_amount'])) unset($order['fixed_amount']);
        if ( empty($order['decide_amount'])) unset($order['decide_amount']);
        $this->order->save($order);
      } else {
        return redirect()->back()->with('error', '주문 정보를 불러오는 데 오류 발생. 다시 시도');
      }
    }

    if ( !empty($packaging) ) {
      $packagingDetailIds = []; // 수정해야할 detail id
      $nextStepStatus = [];
      $hasNextStep = false;

      $packagingDetails = $this->packagingDetail
                              ->packagingDetailJoinStatus(['packaging_detail.packaging_id' => $packaging['packaging_id']
                                                          , 'complete' => 0])
                              ->findAll();

      if ( !empty($packagingDetails) ) {
        foreach( $packagingDetails AS $i => $packagingDetail ) {
          // 중복삭제 할 것
          if ( $packagingDetail['order_by'] < $packaging['order_by'] ) {
            $this->packagingDetail->save(['idx' => $packagingDetail['idx'], 'complete' => 1]);
          }
        }
      } 

      $packagingDetailIds = $this->packagingDetail
                                ->packagingDetailJoinStatus(['packaging_detail.packaging_id' => $packaging['packaging_id']
                                                            , 'status_id' => $packaging['status_id']])
                                ->findAll();    
      
      if ( empty($packagingDetailIds) ) {
        $packagingStatus = $this->packagingStatus->where(['available' => 1])->orderBy('order_by ASC')->findAll();
        // if ( !empty($packagingStatus) ) {
        //   foreach($packagingStatus AS $p => $pStatus) {
        //     if ( $pStatus['idx'] == $packaging['status_id'] ) {
        //       if ( !empty($pStatus['next_step']) && !is_null($pStatus['next_step_index']) ) {
        //         $hasNextStep = true;
        //         $complete = [];                
        //         for($i = 0; $i < $pStatus['next_step']; $i++ ) {
        //           if ( $i < ($pStatus['next_step'] - 1) ) $complete = ['complete' => 1];
        //           else $complete = [];
        //           $nextStepStatus = array_merge(['packaging_id' => $packaging['packaging_id']
        //                                         , 'status_id' => $packagingStatus[$pStatus['next_step_index']]['idx']]
        //                                 , $complete);
        //         }
        //       }
        //       break;
        //     }
        //   }
        // }
        
        if ( array_key_exists('order_by', $packaging) ) { unset($packaging['order_by']); }
        
        $tempDetail = $this->packagingDetail->where($packaging)->first();
        if ( !empty($tempDetail) ) { $packaging['idx'] = $tempDetail['idx']; }
        
        if ( $hasNextStep ) $packaging['complete'] = 1;
        if ( $this->packagingDetail->save($packaging) ) {
          if ( $hasNextStep ) {
            if ( !$this->packagingDetail->save($nextStepStatus) ) {
              return redirect()->back()->with('error', 'insert error');
            }
          }
        } else return redirec()->back()->with('error', 'packaging insert error');
      }
    } else {
      // 주문에 해당하는 packaging detail 자체가 없음.
    }
    return redirect()->back();
  }

  public function getCurrentStepPackageStatus($packagingDetail = []) {
    $status = [];
    $packagingStatus = $this->packagingStatus->where('available', 1)->orderBy('order_by ASC')->findAll();

    if ( !empty($packagingStatus) ) {
      foreach($packagingStatus AS $p => $pStatus) :
        if ( !is_null($pStatus['department_ids']) ) {
          $departmentIDs = explode(",", $pStatus['department_ids']);
          if ( !in_array(session()->userData['department'], $departmentIDs) ) {
            $packagingStatus[$p]['has_email'] = false;
            $packagingStatus[$p]['email_id'] = false;
          }
        } else {
          $packagingStatus[$p]['has_email'] = false;
          $packagingStatus[$p]['email_id'] = false;
        }

        if ( !empty($packagingDetail) ) {
          if ( $pStatus['order_by'] == $packagingDetail['order_by']) {
            $index = $p;

            array_push($status, $packagingStatus[$index]); // 현재단계
            if ( count($packagingStatus) > $index ) { // 다음단계
              array_push($status, $packagingStatus[$index + 1]);
            }
          break;
        }
      } else $status = $packagingStatus;
      endforeach;
    }

    return $status;
  }

  public function getOrders() {
    return $this->order
                ->orderJoin()
                ->buyerJoin()
                ->packagingJoin()
                ->productWeight()
                ->paymentStatusJoin()
                ->deliveryJoin()
                ->paymentJoin()
                ->select('buyers.name AS buyer_name, buyers.id AS buyer_id')
                ->select('users.email AS user_email')
                ->select('packaging_status.status_name')
                ->select('receipt_group.payment_status_group')
                ->select('payment_method.id AS payment_method_id
                        , payment_method.payment
                        , payment_method.payment_val')
                ->where('orders.available', 1)
                ->where(['packaging_detail.in_progress' => 1, 'packaging_detail.complete' => 0]);
  }

  public function getOrder($orderId = null) {
    if ( !empty($orderId) ) {
      $this->order
            ->select('ROUND(IFNULL(amount_paid.amount_paid, 0), 2) AS amount_paid')
            ->join("( SELECT order_id, SUM(rq_amount) AS amount_paid 
                      FROM orders_receipt 
                      WHERE order_id = {$orderId} AND payment_status = 100
                    ) AS amount_paid", 'amount_paid.order_id = orders.id', 'left outer')
            ->where('orders.id', $orderId);
    }

    return $this->order
                ->orderJoin()
                ->packagingJoin()
                ->productWeight()
                ->buyerJoin()
                ->deliveryJoin()
                ->paymentJoin()
                ->receiptJoin()
                ->select('buyers.name AS buyer_name')
                ->select('users.idx AS user_idx, users.name AS user_name, users.email AS user_email')
                ->select('manager.name AS manager_name, manager.email AS manager_email')
                ->select('buyers_address.consignee, buyers_address.region,
                          buyers_address.streetAddr1, buyers_address.streetAddr2,
                          buyers_address.zipcode, buyers_address.phone_code AS phonecode, buyers_address.phone')
                // ->select('SUM(IFNULL(delivery.delivery_price, 0)) AS delivery_price')
                ->select('orders_receipt.receipt_type, orders_receipt.payment_status')
                ->select('payment_method.payment');
  }

  public function getOrderDetail($orderId = null) {
    return $this->data['details'] = $this->orderDetail
                                        ->productBrandJoin()
                                        ->join('orders', 'orders.id = orders_detail.order_id', 'RIGHT')
                                        ->join("currency_rate", "currency_rate.cRate_idx = orders.currency_rate_idx", "LEFT OUTER")
                                        ->join("currency", "currency.idx = currency_rate.currency_idx", "LEFT OUTER")
                                        ->select('orders_detail.*')
                                        ->select('currency.currency_sign AS currency_sign')
                                        ->select('currency.currency_float AS currency_float')
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

  public function paypalCarftInvoice() {

  }

  public function paypalSendInvoice() {

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