<main class='position-relative'>
  <title>주문상세</title>
  <div class='order-detail-container'>
    <div>
      <label>업체(buyer)명</label>
      <div class='con'>
        <?=$order['buyer_name']?><br/>
        <?php if ( !empty($order['user_email']) ) :
          echo mailto($order['user_email'], $order['user_id']."(".$order['user_name'].")");
        else :
          echo "{$order['user_id']}({$order['user_name']})";
        endif;
        ?>
      </div>
    </div>
    <div>
      <label>담당자명</label>
      <div class='con'>
        <?=$order['manager_name']?>(<?=$order['manager_email']?>)
      </div>
    </div>
    <div>
      <label>order number</label>
      <div class='con'><?=$order['order_number']?></div>
    </div>
    <div>
      <label>order date</label>
      <div class='con'><?=$order['created_at']?></div>
    </div>
    <div>
      <label>주문 통화 단위</label>
      <div class='con'><?=$order['currency_code']?></div>
    </div>
    <div>
      <label>영/과세 여부</label>
      <div class='con'><?=$order['taxation'] == 1 ? '영세' : '과세' ?></div>
    </div>
    <div>
      <label>Invoice 관리</label>
      <div class='con d-flex flew-row flex-wrap'>
        <table>
          <thead>
            <tr>
              <th rowspan='2' style='width: 2%'>No</th>
              <th colspan='3' style='width: 25%'>결제금액(<?=$order['currency_code']?>)</th>
              <!-- <th colspan='2' style='width: 10%'>배송비</th> -->
              <!-- <th rowspan='2' style='width: 10%'>남은금액</th> -->
              <th rowspan='2' style='width: 8%'>결제현황</th>
              <th rowspan='2' style='width: 8%'>표시여부</th>
              <th rowspan='2' style='width: 15%'>결제수단</th>
              <th rowspan='2' style='width: 20%'></th>
            </tr>
            <tr>
              <th>상품가격</th>
              <th>배송비</th>
              <th>남은금액</th>
            </tr>
          </thead>
          <tbody>
          <?php if ( !empty($receipts) ) : 
          foreach($receipts as $i => $receipt) : ?>
            <tr>
              <td><?=$receipt['receipt_type']?>차</td>
              <td class='text-end'>
                <?=$order['currency_sign'] . number_format(($receipt['rq_amount'] + $receipt['delivery_price']), $order['currency_float'])?>
              </td>
              <td class='text-end'>
                <?=$order['currency_sign'] . number_format($receipt['delivery_price'], $order['currency_float'])?>
              </td>
              <td class='text-end'>
                <?=$order['currency_sign'] . number_format($receipt['due_amount'], $order['currency_float'])?>
              </td>
              <td class='text-center'>
                <?php echo esc($status->paymentStatus[$receipt['payment_status']] ) ?>
              </td>
              <td class='text-center'>
                <?=$receipt['display'] == 1 ? '표시 중' : '표시 안함'?>
              </td>
              <td class='text-start'>
                <?=esc($order['payment'])?>
                <?php if ( $order['payment'] == 'Paypal') : ?>
                <a class='btn-link' href='<?=$receipt['payment_url']?>' target='_blank'><?=esc($receipt['payment_invoice_id'])?></a>
                <?php endif; ?>
              </td>
              <td class='text-center pt-2 pb-1'>
                <form action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8">
                <input type='hidden' name='receipt_id' value='<?=$receipt['receipt_id']?>'>
                <input type='hidden' name='order_id' value='<?=$order['id']?>'>
                <input type='hidden' name='payment_status' value='<?=$receipt['payment_status']?>'>
                <input type='hidden' name='payment_invoce_id' value='<?=$receipt['payment_invoice_id']?>'>
                <input type='hidden' name='piControllType' value>
                <?php if ( $order['payment'] == 'Paypal' && $order['order_check'] >= 0 && $receipt['payment_status'] == 0 ) : ?>
                <div class='btn btn-sm btn-secondary btn-pi payment_status_check' data-type=''>결제현황 확인</div>
                <?php endif ?>
                <?php if ( $receipt['payment_status'] == 0 ) : ?>
                <div class='btn btn-sm btn-secondary btn-pi' data-type='cancel'>취소</div>
                <?php endif; ?>
                <div class='btn btn-sm btn-secondary btn-pi' data-type='edit'>수정</div>
                <?php if ( $receipt['payment_status'] == 100 && $receipt['due_amount'] > 0 ) : 
                  if (count($receipts) <= ($i + 1)) : ?>
                  <input type='hidden' name='request_amount' value='<?=$receipt['due_amount']?>'>
                  <!-- <input type='hidden' name='remain_amount' value='0'> -->
                  <input type='hidden' name='buyer_name' value='<?=$order['buyer_name']?>'>
                  <input type='hidden' name='user_id' value='<?=$order['user_id']?>'>
                  <input type='hidden' name='user_idx' value='<?=$order['user_idx']?>'>
                  <input type='hidden' name='receipt_type' value='<?=($receipt['receipt_type'] + 1)?>'>
                <div class='btn btn-sm btn-secondary btn-pi' data-type='receipt'><?=($receipt['receipt_type'] + 1)?>차 발행</div>
                <?php endif;
                endif; ?>
                <?php if ( $receipt['payment_status'] == 100 ) : ?>
                  <div class='btn btn-sm btn-secondary btn-pi' data-type='refund'>환불</div>
                  <?php if ( $receipt['due_amount'] == 0 && $order['complete_payment'] == 1) : ?>
                  <!-- <div class='btn btn-sm btn-secondary btn-pi' data-type='ci'>CI 발행</div> -->
                  <?php endif;?>
                <?php endif; ?>
                </form>
              </td>
            </tr>
        <?php endforeach;
        endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div>
      <label>배송</label>
      <div class='con'>
        <div class='w-60p d-flex flex-column flex-wrap border border-dark p-0'>
          <div class='w-100 d-flex flex-row table-thead'>
            <div class='w-20p table-thead-th'>배송사</div>
            <div class='w-35p table-thead-th'>배송비</div>
            <div class='w-15p table-thead-th'>산정완료</div>
            <div class='w-15p table-thead-th'>적용 PI</div>
            <div class='w-15p table-thead-th'>기타</div>
            <div class='w-15p table-thead-th'>배송요청</div>
          </div>
          <?php if ( !empty($deliveries) ) : 
          foreach($deliveries as $i => $delivery) : ?>
          <div class='w-100 d-flex'>
            <form class='w-100 d-flex flex-row table-tbody' action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8">
            <div class='w-20p table-tbody-td text-center border border-dark border-0 border-end py-2'>
              <input type='hidden' name='delivery[<?=$i?>][payment]' value=<?=!empty($order) ? $order['payment'] : ''?> >
              <input type='hidden' name='delivery[<?=$i?>][currency_code]' value=<?=!empty($order) ? $order['currency_code'] : ''?> >
              <select name='delivery[<?=$i?>][shipment_id]' required <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
                <option value=''>-</option>
                <?php if ( !empty($shipments) ) : 
                foreach($shipments as $shipment) : ?>
                <option value='<?=$shipment['id']?>' 
                        <?=$delivery['shipment_id'] == $shipment['id'] ? 'selected' : ''?>>
                  <?=$shipment['shipment_name_en']?>
                </option>
                <?php endforeach;
                endif; ?>
              </select>
            </div>
            <div class='w-35p table-tbody-td text-center border border-dark border-0 border-end py-2'>
              <input type='text' name='delivery[<?=$i?>][delivery_price]'
                  placeholder='1234.05'
                  value='<?=!empty($delivery['delivery_price']) ? $delivery['delivery_price'] : ''?>' 
                  pattern='[0-9]+([\.][0-9]{0,2})?'
                  <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>
                  required>
            </div>
            <div class='w-15p table-tbody-td text-center border border-dark border-0 border-end py-2'>
              <input type='checkbox' 
                class='delivery_code' 
                name='delivery[<?=$i?>][delivery_code]' 
                <?=$delivery['delivery_code'] > 0 ? "checked" : ""?>
                <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
            </div>
            <div class='w-15p table-tbody-td text-center border border-dark border-0 border-end py-2'>
              <select name='delivery[<?=$i?>][receipt_id]' <?=$delivery['delivery_code'] != 0 ? 'disabled' : ''?>>
                <option>선택</option>
              <?php if ( !empty($receipts) ) : 
                foreach ( $receipts AS $receipt ) : ?>
                <option value='<?=$receipt['receipt_id']?>' <?=$receipt['delivery_id'] == $delivery['id'] ? 'selected': ''?>>
                  <?=$receipt['receipt_type']?>차 PI
                </option>
              <?php endforeach;
              endif; ?>
              </select>
            </div>
            <div class='w-15p table-tbody-td text-center border border-dark border-0 border-end py-2'>
              <input type='hidden' name='piControllType' value='delivery'>
              <input type='hidden' name='delivery[<?=$i?>][order_id]' value='<?=$order['id']?>' <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
              <input type='hidden' name='delivery[<?=$i?>][id]' value='<?=$delivery['id']?>' <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
              <?php if ( $delivery['delivery_code'] == 0 ) : ?>
              <button class='btn btn-secondary btn-sm btn-pi display-except' data-type='delivery'>
                <?php echo (empty($delivery['delivery_price']) ? '등록' : '수정')?>
              </button>
              <?php else : ?>
                <span class='text-danger' style='font-size: 0.5rem'>해당 인보이스에서 수정</span>
              <?php endif; ?>
            </div>
            </form>
            <div class='w-15p table-tbody-td'>
              
            </div>
          </div>
          <?php endforeach;
          endif; ?>
        </div>
        <form action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8">
        <table class='w-50'>
          <colgroup>
            <col width='20%;'>
            <col width='auto;'>
            <!-- <col width='15%;'> -->
            <col width='15%;'>
            <col width='15%;'>
            <col width='15%;'>
            <col width='auto;'>
          </colgroup>
          <thead>
            <tr>
              <th>배송사</th>
              <th>배송비</th>
              <!-- <th>Forward</th> -->
              <th>산정완료</th>
              <th>적용 PI</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if ( !empty($deliveries) ) : 
            foreach($deliveries as $i => $delivery) : ?>
            <tr>
              <td>
                <input type='hidden' name='delivery[<?=$i?>][payment]' value=<?=!empty($order) ? $order['payment'] : ''?> >
                <input type='hidden' name='delivery[<?=$i?>][currency_code]' value=<?=!empty($order) ? $order['currency_code'] : ''?> >
                <select name='delivery[<?=$i?>][shipment_id]' required <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
                  <option value=''>-</option>
                  <?php if ( !empty($shipments) ) : 
                  foreach($shipments as $shipment) : ?>
                  <option value='<?=$shipment['id']?>' 
                          <?=$delivery['shipment_id'] == $shipment['id'] ? 'selected' : ''?>>
                    <?=$shipment['shipment_name_en']?>
                  </option>
                  <?php endforeach;
                  endif; ?>
                </select>
              </td>
              <td>
                <input type='text' name='delivery[<?=$i?>][delivery_price]'
                    placeholder='1234.05'
                    value='<?=!empty($delivery['delivery_price']) ? $delivery['delivery_price'] : ''?>' 
                    pattern='[0-9]+([\.][0-9]{0,2})?'
                    <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>
                    required>
              </td>
              <!-- <td>
                <input type='checkbox' 
                  class='forward' 
                  name='delivery[<?//=$i?>][forward]' 
                  <?//=$delivery['forward'] == 1 ? 'checked' : ''?> 
                  <?//=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
              </td> -->
              <td>
                <input type='checkbox' 
                  class='delivery_code' 
                  name='delivery[<?=$i?>][delivery_code]' 
                  <?=$delivery['delivery_code'] > 0 ? "checked" : ""?>
                  <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
              </td>
              <td>
                <select name='delivery[<?=$i?>][receipt_id]' <?=$delivery['delivery_code'] != 0 ? 'disabled' : ''?>>
                  <option>선택</option>
                <?php if ( !empty($receipts) ) : 
                  foreach ( $receipts AS $receipt ) : ?>
                  <option value='<?=$receipt['receipt_id']?>' <?=$receipt['delivery_id'] == $delivery['id'] ? 'selected': ''?>>
                    <?=$receipt['receipt_type']?>차 PI
                  </option>
                <?php endforeach;
                endif; ?>
                </select>
              </td>
              <td>
                <input type='hidden' name='piControllType' value='delivery'>
                <input type='hidden' name='delivery[<?=$i?>][order_id]' value='<?=$order['id']?>' <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
                <input type='hidden' name='delivery[<?=$i?>][id]' value='<?=$delivery['id']?>' <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
                <?php if ( $delivery['delivery_code'] == 0 ) : ?>
                <button class='btn btn-secondary btn-sm btn-pi display-except' data-type='delivery'>
                  <?php echo (empty($delivery['delivery_price']) ? '등록' : '수정')?>
                </button>
                <?php else : ?>
                  <span class='text-danger' style='font-size: 0.5rem'>해당 인보이스에서 수정</span>
                <?php endif; ?>
              </td>
            </tr>            
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
        </form>
      </div>
    </div>
    <div>
      <label>배송현황</label>
      <div class='con'>
        <form method='post' action='<?=base_url('packaging/packagingRequest')?>'>
          <input type='hidden' name='order_id' value='<?=$order['id']?>'>
          <?php if ( empty($packaging) && !isset($packaging['idx']) ) : ?>
          <input type='submit' class='btn btn-secondary btn-sm' value='배송요청'>
          <?php else : ?>
          <input type='hidden' name='id' value='<?=$packaging['idx']?>'>
          <input type='hidden' name='detail_id' value='<?=$packaging['detail_idx']?>'>
          <input type='submit' class='btn btn-secondary btn-sm' value='배송취소'>
          <?php endif; ?>
        </form>
      </div>
    </div>
    <div>
      <label>order amount</label>
      <div class='con'>
        <table class='w-50'>
          <colgroup>
            <col style='width: 8%;'>
            <col style='width: 8%;'>
            <col style='width: 8%;'>
            <col style='width: 8%;'>
            <col style='width: 8%;'>
          </colgroup>
          <thead>
            <tr>
              <th>결제수단</th>
              <th>주문금액</th>
              <th>구간적용</th>
              <th>배송비</th>
              <th>총 금액</th>
            </tr>
          <thead>
          <tbody>
            <tr>
              <td><?=esc($order['payment'])?>
              <td><?=$order['currency_sign'] . number_format($order['order_amount'], $order['currency_float'])?></td>
              <td><?=$order['currency_sign'] . number_format($order['discount_amount'], $order['currency_float'])?></td>
              <td><?=$order['currency_sign'] . number_format($order['delivery_price'], $order['currency_float'])?></td>
              <td><?=$order['currency_sign'] . number_format(($order['subtotal_amount'] + $order['delivery_price']), $order['currency_float'])?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div>
      <label>배송정보</label>
      <div class='con'>
        <p class='fw-bold m-0 p-0'><?=$order['consignee']?></p>
        <p class='m-0 p-0'>
          <?=$order['region']?><br/>
          <?=$order['streetAddr1'] . $order['streetAddr2']?>
        </p>
        <p class='m-0 p-0'><?=$order['zipcode']?></p>
        <p class='m-0 p-0'><?="+".$order['phonecode']."-".$order['phone']?></p>
      </div>
    </div>
    <div>
      <label>주문상품</label>
      <div class='con'>
        <table>
          <colgroup>
            <col style='width: 3%;'>
            <col style='width: 5%;'>
            <col style='width: 5%;'>
            <col style='width: 8%;'>
            <col style='width: 30%;'>
            <col style='width: 10%;'>
            <col style='width: 6%;'>
            <col style='width: 6%;'>
            <col style='width: 6%;'>
            <col style='width: 6%;'>
            <col style='width: 6%;'>
            <col style='width: 8%;'>
          </colgroup>
          <thead>
            <tr>
              <th rowspan='2'>no</th>
              <th rowspan='2'>주문 제외/취소</th>
              <th rowspan='2'>주문변경</th>
              <th rowspan='2'>Brand</th>
              <th colspan='3'>Description</th>
              <th rowspan='2'>수량</th>
              <th rowspan='2'>가격</th>
              <th rowspan='2'>할인</th>
              <th rowspan='2'>총가격</th>
              <th rowspan='2'>재고요청여부</th>
            </tr>
            <tr>
              <th>제품명</th>
              <th>옵션</th>
              <th>용량</th>
            </tr>
          </thead>
          <tbody>
        <?php if ( !empty($details) ) :
          foreach($details as $i => $detail) : ?>
          <tr>
            <td><?=$i + 1?></td>
            <td><?=!empty($order['change_order_id']) ? $order['change_order_id'] : '-'?></td>
            <td><?=$detail['order_excepted'] == 1 ? '주문제외' : '-'?></td>
            <td class='text-uppercase'><?=$detail['brand_name']?></td>
            <td><?=$detail['prd_name'] . "<br/>(". $detail['prd_name_en'] . ")"?></td>
            <td><?=!empty($detail['type']) ? "#".$detail['type'] : ''?></td>
            <td><?=$detail['spec']?></td>
            <td><?=$detail['prd_order_qty']?></td>
            <?php if ( $detail['order_excepted'] == 0 ) : ?>
            <td><?=$detail['prd_price']?></td>
            <td><?=$detail['prd_discount']?></td>
            <td><?=(($detail['prd_price'] - $detail['prd_discount']) * $detail['prd_order_qty'])?></td>
            <?php else : ?>
            <td>
              <p class='text-decoration-line-through'><?=$detail['prd_price']?></p>
              <p>0</p>
            </td>
            <td>
              <p class='text-decoration-line-through'><?=$detail['prd_discount']?></p>
              <p>0</p>
            </td>
            <td>
              <p class='text-decoration-line-through'><?=(($detail['prd_price'] - $detail['prd_discount']) * $detail['prd_order_qty'])?></p>
              <p>0</p>
            </td>
            <?php endif; ?>
            <td><?=!empty($detail['stock_req']) && $detail['stock_req'] == 1 ? '재고요청' : ''?></td>
          </tr>
        <?php endforeach; 
        endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- <div>
      <label>order number</label>
      <div class='con'>
      </div>
    </div> -->
  </div>
  <div class='invoice-edit position-fixed top-0 start-0 d-none w-100 bg-dark bg-opacity-25 overflow-auto h-100'>
  </div>
</main>