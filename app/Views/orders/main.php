<main>
  <form method='GET' action='<?=base_url('orders')?>'>
  <input type='hidden' name='page' value='1'>
  <fieldset class='my-2 px-2 pb-2 border border-secondary d-flex flex-row justify-content-between align-items-end'>
    <div class='form-group'>
      <label>주문번호</label>
      <input class='form-control form-control-sm w-100'
          type='text' 
          name='order_number' 
          value='<?=isset($_GET['order_number']) ? $_GET['order_number'] : ''?>'>
    </div>
    <input type='submit' class='btn btn-primary' value='검색' />
  </fieldset>
  </form>
  <title>주문내역</title>
  <div>    
    <table>
      <colgroup>
        <col style='width: 3%;'>
        <col style='width: 6%;'>
        <col style='width: 5%;'>
        <col style='width: 5%;'>
        <col style='width: 4%;'>
        <col style='width: 4%;'>
        <col style='width: 7%;'>
        <col style='width: 7%;'>
        <col style='width: 7%;'>
        <col style='width: 7%;'>
        <col style='width: 7%;'>
        <col style='width: 5%;'>
        <col style='width: 5%;'>
      </colgroup>
      <thead>
        <tr>
          <th rowspan='2'>No.</th>
          <th rowspan='2'>Order Number</th>
          <th rowspan='2'>결제수단</th>
          <th rowspan='2'>영/과세 여부</th>
          <th rowspan='2'>총무게</br><span style='font-size: 0.5rem;'>(단위:g)</span></th>
          <th colspan='6'>주문합계</th>
          <th rowspan='2'>기타</th>
          <th rowspan='2'></th>
        </tr>
        <tr>
          <th>주문 통화 단위</th>
          <th>주문금액</th>
          <th>할인금액</th>
          <th>배송비</th>
          <th>주문 합계</th>
          <th class='border border-end border-dark'>결제현황</th>
        </tr>
        <tbody>
          <?php if ( !empty($orders) ) : 
            foreach($orders AS $i => $order) : ?>
          <tr>
            <td><?=$i + 1?></td>
            <td><?=$order['order_number']?></td>
            <td><?=$order['payment']?></td>
            <td><?=$order['taxation'] = 1 ? '영세' : '과세'?></td>
            <td><?=number_format($order['shipping_weight'])?>g</td>
            <td><?=$order['currency_code']?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['order_amount'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['discount_amount'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['delivery_price'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['subtotal_amount'], $order['currency_float'])?></td>
            <td class='text-start px-2'>
              <?php if (!is_null($order['payment_status_group'])) :
                $payment_status = explode(',', $order['payment_status_group']);
                foreach ( $payment_status as $pStatus) :
                  $payStatus = explode(':', $pStatus);
                  if ( $payStatus[1] < 0 ) {
                    echo "<p class='text-danger'>";
                  } else {
                    echo "<p>";
                  }
                  echo $payStatus[0].'차 : '.$status->paymentStatus($payStatus[1]);
                  echo "</p>";
                endforeach;
              endif;
              ?>
            </td>
            <td><?=$order['order_check'] == 0 ? '-' : '특이사항있음' ?></td>
            <td><a class='btn btn-sm btn-secondary' href='/orders/detail/<?=$order['id']?>'>상세보기</a></td>
          </tr>
          <?php endforeach; 
          endif; ?>
        </tbody>
      </thead>
    <table>
  </div>
  <div class='mt-3'>
    <?php echo $orderPager->links(); ?>
  </div>
</main>