<?php if ( !empty($receipts) ) : ?>
<div class='w-100'>
  <div class=''>

  </div>
  <div>
    
  </div>
</div>
<table class='w-100'>
  <thead>
    <tr>
      <th rowspan='2' style='width: 5%'>No</th>
      <th colspan='3' style='width: 25%'>결제금액(<?=$order['currency_code']?>)</th>
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
      <td class='text-center pt-2 pb-1 form'>
        <form action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8">
        <input type='hidden' name='receipt_id' value='<?=$receipt['receipt_id']?>'>
        <input type='hidden' name='order_id' value='<?=$order['id']?>'>
        <input type='hidden' name='payment_status' value='<?=$receipt['payment_status']?>'>
        <input type='hidden' name='payment_invoce_id' value='<?=$receipt['payment_invoice_id']?>'>
        <input type='hidden' name='piControllType' value>
        <?php if ( $order['payment'] == 'Paypal' && $receipt['payment_status'] == 0 ) : ?>
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
<?php endif; ?>