<div class='pi-edit-container py-2 px-4 my-1 mx-auto w-80p bg-light'>
  <h3 class='mb-1'>주문 수정</h3>
  <?php if ( session()->has('error') ) : ?>
    <div class="notification error">
      <?= session('error') ?>
    </div>
  <?php endif; ?>
  <?=form_open('/orders/pInvoice')?>

  <input type='hidden' name='order_amount' value='<?=$order['order_amount']?>'>
  <input type='hidden' name='amount_paid' value='<?=$order['amount_paid']?>'>
  <input type='hidden' name='order_id' value='<?=$order['id']?>'>
  <input type='hidden' name='piControllType' value='edit'>
  <input type='hidden' name='currency_code' value='<?=$order['currency_code']?>'>
  <div>
    <h6>주문 정보</h6>
    <table class='w-80p'>
      <colgroup>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
      </colgroup>
      <thead>
        <tr>
          <th>주문번호</th>
          <th>주문금액</th>
          <th>주문할인금액</th>
          <th>상품 총 주문금액</th>
          <th>배송비</th>
          <th>지불된 금액</th>
          <th>남은 금액</th>
          <th>취소 금액</th>
          <th>결제수단</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?=$order['order_number']?></td>
          <td><?=$order['currency_sign'] . number_format($order['order_amount'], $order['currency_float'])?></td>
          <td><?=$order['currency_sign'] . number_format($order['discount_amount'], $order['currency_float'])?></td>
          <td><?=$order['currency_sign'] . number_format($order['subtotal_amount'], $order['currency_float'])?></td>
          <td><?=$order['currency_sign'] . number_format($order['delivery_price'], $order['currency_float'])?></td>
          <td><?=$order['currency_sign'] . number_format($order['amount_paid'], $order['currency_float'])?></td>
          <td><?=$order['currency_sign'] . number_format(($order['subtotal_amount'] - $order['amount_paid']), $order['currency_float'])?></td>
          <td><?=$order['currency_sign'] . number_format(($order['subtotal_amount'] - $order['amount_paid']), $order['currency_float'])?></td>
          <td><?=$order['payment']?></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class='mt-3'>
    <h6>PI 수정</h6>
    <table class='w-75'>
      <colgroup>
        <col width='10%;'>
        <col width='15%;'>
        <col width='15%;'>
        <col width='15%;'>
        <col width='15%;'>
        <col width='15%;'>
      </colgroup>
      <thead>
        <tr>
          <th>결제회차</th>
          <th>결제비율</th>
          <th>결제요청금액</th>
          <th>결제 후 잔액</th>
          <th>결제 현황</th>
          <th>입금 날짜</th>
        </tr>
      </thead>
      <tbody>
        <?php if ( !empty($receipt) ) : ?>
        <tr>
          <td>
            <span class='receipt_num'><?=$receipt['receipt_type']?></span>
          </td>
          <td>
            <input type='hidden' name='receipt[invoice_id]' value='<?=$receipt['payment_invoice_id']?>'>
            <input type='hidden' name='receipt[receipt_id]' value='<?=$receipt['receipt_id']?>'>
            <input type='hidden' name='receipt[order_id]' value='<?=$receipt['order_id']?>'>
            <input type='hidden' name='receipt[receipt_type]' value='<?=$receipt['receipt_type']?>'>
            <select name='receipt[rq_percent]' >
              <option>-</option>
              <?php for($i = 10; $i <= 100; $i += 5) { ?>
              <option value='<?=$i / 100?>' 
                <?=($receipt['rq_percent'] * 100) == $i ? 'selected' : '' ?>>
                <?=$i?>
              </option>
              <?php } ?>
            </select>
            %
          </td>
          <td>
            <?=$order['currency_sign']?>
            <input type='hidden' name='receipt[rq_amount]' value='<?=$receipt['rq_amount']?>'>
            <span class='receipt-rq-amount btn btn-sm' data-name='receipt[rq_amount]'>
              <?=number_format($receipt['rq_amount'], $order['currency_float'])?>
            </span>
          </td>
          <td>
            <?=$order['currency_sign']?>
            <input type='hidden' name='receipt[due_amount]' value='<?=$receipt['due_amount']?>'>
            <span class='receipt-due-amount' data-name='receipt[due_amount]'><?=number_format($receipt['due_amount'], $order['currency_float'])?></span>
          </td>
          <td>
            <select name='receipt[payment_status]' <?=$order['payment'] == 'Paypal' ? 'disabled' : ''?>>
              <?php foreach($status->paymentStatus as $key => $status) :
                echo "<option value='".$key."'";
                if ( $key == $receipt['payment_status']) : 
                  echo "selected";
                endif;
                if ( $key < 0 ) :
                  echo "class='d-none'";
                endif;
                echo ">".$status."</option>";
              endforeach ?>
            </select>
          </td>
          <td>
            <!-- <div class='position-relative'> -->
              <input type="text" id='payment_date' name='receipt[payment_date]'
                <?=$order['payment'] == 'Paypal' || empty($receipt['payment_status']) ? 'disabled' : ''?>
                value='<?=!empty($receipt['payment_date']) ? $receipt['payment_date'] : ''?>'>
            <!-- </div> -->
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <p class='text-danger fw-bold' style='font-size: 0.75rem;'>
      * 선수금 비율, 결제요청 금액 수치를 click해서 수정 가능.
    </p>
  </div>
  <div class='mt-3'>
    <h6>표시</h6>
    <div class='d-flex flex-column border border-dark p-2 w-25'>
      <label>
        <input type='checkbox' name='receipt[display]' <?=$receipt['display'] == 1 ? 'value=1 checked' : 'value=0'?> <?=$receipt['receipt_type'] == 1 ? 'disabled' : ''?>>
        영수증 표시
      </label>
      <?php if ( $receipt['receipt_type'] == 1 ) : ?>
      <p class='text-danger mt-1' style='font-size: 0.5rem;'>
        * 1차 영수증은 수정 불가합니다.
      </p>
      <?php endif; ?>
    </div>
  </div>
  <div class='mt-3 shipment_info'>
    <h6>배송정보</h6>
    <table class='w-50'>
      <colgroup>
        <col width='10%;'>
        <col width='30%;'>
        <col width='40%;'>
        <!-- <col width='10%;'> -->
        <col width='10%;'>
      </colgroup>
      <thead>
        <tr>
          <th>적용</th>
          <th>배송사</th>
          <th>배송비</th>
          <!-- <th>Forward</th> -->
          <th>산정완료</th>
        </tr>
      </thead>
      <tbody>
        <?php if ( !empty($deliveries) ) : 
        foreach($deliveries as $i => $delivery) : ?>
        <input type='hidden' name='delivery[<?=$i?>][id]' value='<?=$delivery['id']?>'>
        <tr>
          <td>
            <input type='checkbox'
              name='receipt[delivery_id]' 
              class='check_except required' 
              data-value='<?=$delivery['id']?>' 
              <?=$delivery['id'] == $receipt['delivery_id'] || !empty($delivery['shipment_id']) ? 'value="'.$delivery['id'].'" checked' : 'value=0'?>
              <?=($delivery['delivery_code'] == 100) ? 'disabled' : ''?>>
          </td>
          <td>
            <select name='delivery[<?=$i?>][shipment_id]' class='required'>
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
            <input type='text' 
              name='delivery[<?=$i?>][delivery_price]' 
              class='required' 
              value='<?=!empty($delivery['delivery_price']) ? $delivery['delivery_price'] : '0'?>'>
          </td>
          <!-- <td>
            <input type='checkbox' 
              name='delivery[<?=$i?>][forward]' 
              <?//=$delivery['forward'] == 1 ? 'value=1 checked' : 'value=0'?>
              <?//=($delivery['delivery_code'] == 100) ? 'disabled' : ''?>>
          </td> -->
          <td>
            <input type='checkbox' 
              name='delivery[<?=$i?>][delivery_code]' 
              class='check_except' 
              data-value='100' 
              <?=$delivery['delivery_code'] == 100 ? 'value=100 checked' : 'value=0'?>
              <?=($delivery['delivery_code'] == 100) ? 'disabled' : ''?>>
          </td>
        </tr>
        <?php endforeach;
        endif; ?>
      </tbody>
    </table>
  </div>
  <div class='mt-3'>
    <h6 class='mb-1'>주문 상품 수정</h6>
    <table class='w-100'>
      <colgroup>
        <col style='width: 3%;'>
        <col style='width: 3%;'>
        <col style='width: 7%;'>
        <col style='width: 8%;'>
        <col style='width: 8%;'>
        <col style='width: 23%;'>
        <col style='width: 10%;'>
        <col style='width: 6%;'>
        <col style='width: 5%;'>
        <col style='width: 4%;'>
        <col style='width: 5%;'>
        <col style='width: 6%;'>
        <col style='width: 8%;'>
      </colgroup>
      <thead>
        <tr>
          <th rowspan='2'>no</th>
          <th rowspan='2'>제외</th>
          <th rowspan='2'>Brand</th>
          <th rowspan='2'>HS Code</th>
          <th rowspan='2'>유통기한</th>
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
          <td>
            <?=$i + 1?>
            <input type='hidden' name='detail[<?=$i?>][id]' value='<?=$detail['id']?>'>
          </td>
          <td>
            <input type='hidden' name='detail[<?=$i?>][order_excepted]' value='<?=$detail['order_excepted']?>'>
            <input type='checkbox' class='detail-order-excepted' <?=$detail['order_excepted'] == 1 ? 'checked' : ''?>>
          </td>
          <td class='text-uppercase'>
            <?=$detail['brand_name']?>
          </td>
          <td class='text-start'>
            <input type='hidden' name='product[<?=$i?>][id]' value='<?=$detail['product_idx']?>'>
            <input type='text' name='product[<?=$i?>][hs_code]' class='form-control form-control-sm' value=<?=!empty($detail['hs_code']) ? $detail['hs_code'] : ''?>>
          </td>
          <td>
            <input type='text' name='detail[<?=$i?>][expiration_date]' class='form-control form-control-sm expiration_date' value='<?=!empty($detail['expiration_date']) ? $detail['expiration_date'] : ''?>'>
          </td>
          <td class='text-start'>
            <?=$detail['prd_name'] . "<br/>". $detail['prd_name_en']?>
          </td>
          <td class='text-start'>
            <?=!empty($detail['type']) ? "<p>#".$detail['type']."</p>" : ''?>
            <?=!empty($detail['type_en']) ? "<p>#".$detail['type_en']."</p>" : ''?>
          </td>
          <td>
            <?=$detail['spec']?>
          </td>
          <td>
            <input type='text' 
                  name='detail[<?=$i?>][prd_order_qty]' 
                  class='form-control form-control-sm' 
                  value=<?=$detail['prd_order_qty'] - $detail['prd_changed_qty']?>>
          </td>
          <td><?=$detail['prd_price']?></td>
          <td><?=$detail['prd_discount']?></td>
          <td><?=(($detail['prd_price'] - $detail['prd_discount']) * $detail['prd_order_qty'])?></td>
          <td><?=!empty($detail['stock_req']) && $detail['stock_req'] == 1 ? '재고요청' : ''?></td>
        </tr>
      <?php endforeach; 
      endif; ?>
      </tbody>
    </table>
  </div>
  <div class='text-end mt-4'>
    <div class='btn btn-secondary invoice-edit'>닫기</div>
    <?php if ($receipt['payment_status'] != -200 ) : ?>
    <button class='btn btn-primary'>수정</button>
    <?php endif; ?>
  </div>
</div>