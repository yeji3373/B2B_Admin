<?php if ( !empty($deliveries) ) : ?>
<form action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8">
  <table class='w-100'>
    <colgroup>
      <col width='20%;'>
      <col width='auto;'>
      <!-- <col width='15%;'> -->
      <col width='10%;'>
      <col width='10%;'>
      <col width='10%;'>
      <col width='auto;'>
    </colgroup>
    <thead>
      <tr>
        <th>배송사</th>
        <th>배송비</th>
        <th>Forward</th>
        <th>산정완료</th>
        <th class='receipt_apply'>적용 PI</th>
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
          <select class='w-100' name='delivery[<?=$i?>][shipment_id]' required <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>>
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
          <div class='d-flex flex-row justify-content-between'>
          <select class='w-25' name='delivery_currency_idx'>
            <option value=''>-</option>
            <?php if ( !empty($currency) ) : 
              foreach($currency AS $c) : ?>
              <option value='<?=$c['idx']?>'><?=$c['currency_code']?></option>
            <?php endforeach;
            endif; ?>
          </select>
          <input class='ms-1 w-100' type='text' name='delivery[<?=$i?>][delivery_price]'
              placeholder='1234.05'
              value='<?=!empty($delivery['delivery_price']) ? $delivery['delivery_price'] : ''?>' 
              pattern='[0-9]+([\.][0-9]{0,2})?'
              <?=$delivery['delivery_code'] != 0 ? 'disabled': ''?>
              required>
          </div>
        </td>
        <td>
          <input type='checkbox' 
            class='forward' 
            name='delivery[<?=$i?>][forward]' 
            <?=$delivery['forward'] == 1 ? ' checked' : ''?> 
            <?=$delivery['delivery_code'] != 0 ? ' disabled': ''?>>
        </td>
        <td>
          <input type='checkbox' 
            class='delivery_code' 
            name='delivery[<?=$i?>][delivery_code]' 
            <?=$delivery['delivery_code'] > 0 ? " checked" : ""?>
            <?=$delivery['delivery_code'] != 0 ? ' disabled': ''?>>
        </td>
        <td class='receipt_apply'>
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
<?php endif; ?>