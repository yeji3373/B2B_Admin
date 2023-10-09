<div class='pi-edit-container py-2 px-4 my-1 mx-auto w-80p bg-light'>
  <h3 class='mb-1'>주문 수정</h3>
  <?php if ( session()->has('error') ) : ?>
    <div class="notification error">
      <?= session('error') ?>
    </div>
  <?php endif; ?>
  <?php if ( !empty($order) ) : ?>
  <?=form_open('/orders/pInvoice')?>
  <input type='hidden' name='order_amount' value='<?=$order['order_amount']?>'>
  <input type='hidden' name='amount_paid' value='<?=$order['amount_paid']?>'>
  <input type='hidden' name='order_id' value='<?=$order['id']?>'>
  <input type='hidden' name='piControllType' value='edit'>
  <input type='hidden' name='currency_code' value='<?=$order['currency_code']?>'>
    <div>
      <h6>주문 정보</h6>
      <table class='w-100'>
        <colgroup>
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
    <?php if ( !empty($receipt) ) : ?>
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
        </tbody>
      </table>
      <p class='text-danger fw-bold' style='font-size: 0.75rem;'>
        * 선수금 비율, 결제요청 금액 수치를 click해서 수정 가능.
      </p>
    </div>
    <?php endif; ?>
    <?php if ( !empty($deliveries) ) : ?>
      <!-- <div class='mt-3 shipment_info'>
      <h6>배송정보</h6>
      <div class='w-75'>
        <?//=view('orders/includes/delivery')?>
      </div>
    </div> -->
    <?php endif; ?>
    <div class='text-end mt-4'>
      <div class='btn btn-secondary invoice-edit'>닫기</div>
      <?php if ($receipt['payment_status'] != -200 ) : ?>
      <button class='btn btn-primary'>수정</button>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>