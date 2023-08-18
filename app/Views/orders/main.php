<main>
  <form method='GET' action='<?=base_url('orders')?>'>
  <input type='hidden' name='page' value='1'>
  <fieldset class='my-2 px-2 pb-2 border border-secondary d-flex flex-row justify-content-between align-items-end'>
    <div class='d-flex flex-row'>
      <div class='form-group me-1'>
        <label>주문번호</label>
        <input class='form-control form-control-sm w-100'
            type='text' 
            name='order_number' 
            value='<?=isset($_GET['order_number']) ? $_GET['order_number'] : ''?>'>
      </div>
      <div class='form-group me-1'>
        <label>검색기간(주문)</label>
        <div>
          <input type='text' name='start_date' value='<?=!empty($_GET['start_date']) ? $_GET['start_date'] : ''?>'>
           ~ <input type='text' name='end_date' value='<?=!empty($_GET['end_date']) ? $_GET['end_date'] : ''?>'>
        </div>
      </div>
      <div class='form-group me-1'>
        <label></label>
      </div>
      <div class='form-group'>
        <label>주문현황</label>
        <select class='form-select form-select-sm' name='order_status'>
          <option value=''>전체</option>
          <?php if ( !empty($orderStatus) ) : 
            foreach ( $orderStatus AS $status ) : ?>
            <option value='<?=$status['idx']?>' <?=!empty($_GET['order_status']) ? 'selected' : ''?> ><?=$status['status_name']?></option>
          <?php endforeach;
          endif; ?>
        </select>
      </div>
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
        <col style='width: 6%;'>
        <col style='width: 6%;'>
        <col style='width: 5%;'>
        <col style='width: 4%;'>
        <col style='width: 4%;'>
        <col style='width: 6%;'>
        <col style='width: 6%;'>
        <col style='width: 6%;'>
        <col style='width: 6%;'>
        <col style='width: 6%;'>
        <col style='width: 6%;'>
        <col style='width: 5%;'>
        <col style='width: 5%;'>
      </colgroup>
      <thead>
        <tr>
          <th rowspan='2'>No.</th>
          <th rowspan='2'>주문일/결제일</th>
          <th rowspan='2'>주문번호</th>
          <th rowspan='2'>Buyer</th>
          <th rowspan='2'>결제현황/수단</th>
          <th rowspan='2'>영/과세</th>
          <th rowspan='2'>총무게</br><span style='font-size: 0.5rem;'>(단위:g)</span></th>
          <th colspan='6'>주문합계</th>
          <th rowspan='2'>기타</th>
          <th rowspan='2'></th>
        </tr>
        <tr>
          <th>재고요쳥금액</th>
          <th>확정금액</th>
          <th>주문금액</th>
          <th>할인금액</th>
          <th>배송비</th>
          <th class='border border-end border-dark'>주문 합계</th>
        </tr>
        <tbody>
          <?php if ( !empty($orders) ) : 
            foreach($orders AS $i => $order) : ?>
          <!-- <?php print_r($order)?> -->
          <tr>
            <td><?=$i + 1?></td>
            <td>
              <p><?=$order['created_at']?></p>
            </td>
            <td class='text-start'><?=$order['order_number']?></td>
            <td class='text-start'>
              <p>
                <a class='text-decoration-underline text-info'
                  href='<?=base_url('buyer/detail/'.$order['buyer_id'])?>'>
                  <?=$order['buyer_name']?>
                </a>
              </p>
              <p>
                <?=$order['user_id']?>
                <?=$order['user_email']?>
              </p>
            </td>
            <td class='text-end px-2'>
              <?php 
              if (!is_null($order['payment_status_group'])) :
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
              else :
                echo "<p>결제 전</p><p style='font-size: 0.7rem;'>".$order['status_name']."</p>";
              endif;
              ?>
            </td>
            <td class='text-end px-2'><?=$order['taxation'] = 1 ? '영세' : '과세'?></td>
            <td class='text-end px-2'><?=number_format($order['shipping_weight'])?>g</td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['request_amount'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['inventory_fixed_amount'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['order_amount'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['discount_amount'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['delivery_price'], $order['currency_float'])?></td>
            <td class='text-end px-2'><?=$order['currency_sign'] . number_format($order['subtotal_amount'], $order['currency_float'])?></td>
            <td><?//=$order['order_check'] == 0 ? '-' : '특이사항있음' ?></td>
            <td>
              <a class='btn btn-sm btn-secondary' 
                <?php if ( $order['payment_id'] ) : ?>
                  href='/orders/detail/<?=$order['id']?>'>상세보기
                <?php else: ?>
                  href='/orders/inventoryDetail/<?=$order['id']?>'>재고확인
                <?php endif; ?>
              </a>
            </td>              
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