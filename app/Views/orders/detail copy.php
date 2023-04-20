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
        <?php if ( !empty($receipts) ) : 
          foreach($receipts as $i => $receipt) : ?>
          <div class='pi-group d-flex flex-column border border-dark p-0 w-25'>
            <div class='fw-bold border-bottom border-dark px-2 w-100'>
              <?=$receipt['receipt_type']?>차 결제 정보
            </div>
            <div class='py-1 px-2 w-100'>
              <table class='border-0'>
                <tbody>
                  <tr>
                    <th class='w-25'>표시여부</th>
                    <td class='text-start border-0'>
                      <?=$receipt['display'] == 1 ? '표시 중' : '표시 안함'?>
                    </td>
                  </tr>
                  <tr>
                    <th class='w-25'>결제금액</th>
                    <td class='text-start border-0'>
                      <?=$order['currency_sign'] . number_format($receipt['rq_amount'], $order['currency_float'])?>
                    </td>
                  </tr>
                  <tr>
                    <th>남은 금액</th>
                    <td class='text-start border-0'>
                      <?=$order['currency_sign'] . number_format($receipt['due_amount'], $order['currency_float'])?>
                    </td>
                  </tr>                  
                  <tr>
                    <th>결제 현황</th>
                    <td class='text-start border-0 d-flex flex-row flex-wrap align-items-center'>
                      <?php echo esc($paymentStatus[$receipt['payment_status']] ) ?>
                    </td>
                  </tr>
                  <tr>
                    <th>결제수단</th>
                    <td class='text-start border-0'>
                      <?=esc($order['payment'])?>
                    </td>
                  </tr>
                  <?php if ( $order['payment'] == 'Paypal') : ?>
                  <tr>
                    <th>URL</th>
                    <td class='text-start'>
                      <a href='<?=$receipt['payment_url']?>' target='blank'><?=esc($receipt['payment_invoice_id'])?></a>
                    </td>
                  </tr>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                <form action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8">
                  <tr>
                    <td colspan='2' class='text-center pt-2 pb-1'>
                      <input type='hidden' name='receipt_id' value='<?=$receipt['receipt_id']?>'>
                      <input type='hidden' name='order_id' value='<?=$order['id']?>'>
                      <input type='hidden' name='payment_status' value='<?=$receipt['payment_status']?>'>
                      <input type='hidden' name='payment_invoce_id' value='<?=$receipt['payment_invoice_id']?>'>
                      <input type='hidden' name='piControllType' value>
                      <?php if ( $order['payment'] == 'Paypal' && $order['order_check'] >= 0 && $receipt['payment_status'] < 100 ) : ?>
                      <div class='btn btn-sm btn-secondary btn-pi payment_status_check' data-type=''>결제현황 확인</div>
                      <?php endif ?>
                      <?php if ( $receipt['payment_status'] == 0 ) : ?>
                      <div class='btn btn-sm btn-secondary btn-pi' data-type='cancel'>취소</div>
                      <div class='btn btn-sm btn-secondary btn-pi' data-type='edit'>수정</div>
                      <?php endif; ?>
                      <?php if ( $receipt['payment_status'] == 100 && $receipt['due_amount'] > 0 ) : 
                        if (count($receipts) <= ($i + 1)) : ?>
                        <input type='hidden' name='request_amount' value='<?=$receipt['due_amount']?>'>
                        <input type='hidden' name='remain_amount' value='0'>
                        <input type='hidden' name='buyer_name' value='<?=$order['buyer_name']?>'>
                        <input type='hidden' name='user_id' value='<?=$order['user_id']?>'>
                        <input type='hidden' name='user_idx' value='<?=$order['user_idx']?>'>
                        <input type='hidden' name='receipt_type' value='<?=($receipt['receipt_type'] + 1)?>'>
                      <div class='btn btn-sm btn-secondary btn-pi' data-type='receipt'><?=($receipt['receipt_type'] + 1)?>차 발행</div>
                      <?php endif;
                      endif; ?>
                      <?php if ( $receipt['payment_status'] == 100 ) : ?>
                        <div class='btn btn-sm btn-secondary btn-pi' data-type='refund'>환불</div>
                      <?php endif; ?>
                    </td>
                  </tr>
                  </form>
                </tfoot>
              </table>
            </div>
          </div>
        <?php endforeach;
        endif; ?>
      </div>
    </div>
    <div>
      <label>배송</label>
      <div class='con'>
        <table class='w-50'>
          <colgroup>
            <col width='35%;'>
            <col width='auto;'>
            <col width='15%;'>
            <col width='15%;'>
            <col width='15%;'>
          </colgroup>
          <thead>
            <tr>
              <th>배송사</th>
              <th>배송비</th>
              <th>Forward</th>
              <th>산정완료</th>
              <th>&nbsp;</th>
            </tr>
          </thead>
          <tbody>
            <?php if ( !empty($deliveries) ) : 
            foreach($deliveries as $i => $delivery) : ?>
            <form action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8">
            <input type='hidden' name='order_id' value='<?=$order['id']?>'>
            <input type='hidden' name='delivery[<?=$i?>][id]' value='<?=$delivery['id']?>'>
            <tr>
              <td>
                <select name='delivery[<?=$i?>][shipment_id]'>
                  <option>-</option>
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
                <input type='text' name='delivery[<?=$i?>][delivery_price]' value='<?=!empty($delivery['delivery_price']) ? $delivery['delivery_price'] : '0'?>'>
              </td>
              <td>
                <input type='checkbox' name='delivery[<?=$i?>][forward]' <?=$delivery['forward'] == 1 ? 'value=1 checked' : 'value=0'?>>
              </td>
              <td>
                <input type='checkbox' name='delivery[<?=$i?>][delivery_code]' <?=$delivery['delivery_code'] > 0 ? "value={$delivery['delivery_code']} checked" : 'value=0'?>>
              </td>
              <td>
                <div class='btn btn-secondary btn-sm btn-pi display-except' data-type='edit'>등록</div>
              </td>
            </tr>
            </form>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
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
              <td><?=$order['currency_sign'] . number_format($order['subtotal_amount'], $order['currency_float'])?></td>
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