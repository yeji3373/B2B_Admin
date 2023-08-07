<main class='position-rel0ative'>
  <title>재고 확인 요청 상세</title>
  <form method='post' action='<?=site_url('orders/inventoryEdit')?>'>
  <div class='order-detail-container inventory-detail-container border-0'>
    <div class='d-grid grid-quad border'>
      <div class='d-flex flex-column border border-0 border-end'>
        <label class='border border-0 border-bottom'>업체(buyer)명</label>
        <div class='con'>
          <?=$order['buyer_name']?> 
          <?php if ( !empty($order['user_email']) ) :
            echo mailto($order['user_email'], $order['user_id']."(".$order['user_name'].")");
          else :
            echo "{$order['user_id']}({$order['user_name']})";
          endif;
          ?>
        </div>
      </div>
      <div class='d-flex flex-column border border-0 border-end'>
        <label class='border border-0 border-bottom'>담당자명</label>
        <div class='con'>
          <?=$order['manager_name']?> (<?=$order['manager_email']?>)
        </div>
      </div>
      <div class='d-flex flex-column border border-0 border-end'>
        <label class='border border-0 border-bottom'>주문번호 / 재고요청일자</label>
        <div class='con'><?=$order['order_number']?> / <?=$order['created_at']?></div>
      </div>
      <div class='d-flex flex-column'>
        <label class='border border-0 border-bottom'>재고요청 / 확정 금액</label>
        <div class='con'>
          <input type='hidden' name='order[id]' value='<?=$order['id']?>'>
          <input type='hidden' name='order[request_amount]' value='<?=$order['request_amount']?>'>
          <input type='hidden' 
                name='order[inventory_fixed_amount]' 
                value='<?=$order['inventory_fixed_amount']?>'>
          <?=$order['currency_sign']. number_format($order['request_amount'], $order['currency_float'])?> 
          / <?=$order['currency_sign']. number_format($order['inventory_fixed_amount'], $order['currency_float'])?>
        </div>
      </div>
    </div>
    <div class='mt-3 d-flex flex-column'>
      <div class='text-end mb-2'>
        <?php if ( !empty($nextPackaging) ) : 
          print_r($nextPackaging);?>
        <label class='text-bg-danger'>
          <input type='checkbox' name='packaging[]' class='value-change me-1'>
          재고 요청 확인 완료
        </label>
        <?php endif;?>
        <button class='btn btn-primary'>저장</button>
        <!-- <button class='btn btn-primary confirm'>확정</button> -->
      </div>
      <table>
        <thead>
          <tr>
            <th rowspan='2' style='width: 2%;'>No.</th>
            <th rowspan='2' style='width: 4%;'>브랜드</th>
            <th colspan='4' style='width: auto;'>product</th>
            <th rowspan='2' style='width: 4%;'>주문수량</th>
            <th rowspan='2' style='width: 4%;'>주문취소(제외)</th>
            <th rowspan='2' style='width: 4%;'>주문/요청 총금액</th>
            <th rowspan='2' style='width: auto;'>요청사항</th>
          </tr>
          <tr>
            <th style='width: 3%;'>바코드</th>
            <th style='width: 18%;'>제품명</th>
            <th style='width: 5%;'>Type</th>
            <th style='width: 4%;'>가격</th>
          </tr>
        </thead>
        <tbody>
        <?php if ( !empty($details) ) : 
          foreach ($details AS $i => $detail ) :?>
          <tr class='detail_items'>
            <td>
              <?=$i + 1?>
              <input type='hidden' name='detail[<?=$i?>][detail][id]' value='<?=$detail['id']?>'>
            </td>
            <td><?=strtoupper(htmlspecialchars(stripslashes($detail['brand_name'])))?></td>
            <td>
              <p><?=$detail['barcode']?></p>
              <?=($detail['sample']) ? '<p>sample</p>' : ''?>
            </td>
            <td class='text-start'>
              <div class='d-flex flex-column'>
                <p><?=$detail['prd_name']?><?=$detail['spec']?></p>
                <p><?=$detail['prd_name_en']?> <?=$detail['spec']?></p>
              </div>
            </td>
            <td class='text-start'>
              <div class='d-flex flex-column'>
                <p><?=$detail['type']?></p>
                <p><?=$detail['type_en']?></p>
              </div>
            </td>
            <td>
              <?=$detail['currency_sign']?>
              <input type='text'
                    data-compare-value='<?=$detail['prd_price_changed'] ? $detail['prd_change_price'] : $detail['prd_price']?>'
                    data-compare-target='detail[<?=$i?>][detail][prd_price_changed]'
                    name='detail[<?=$i?>][detail][prd_change_price]' 
                    value='<?=$detail['prd_price_changed'] ? $detail['prd_change_price'] : $detail['prd_price']?>' 
                    style='width: 5rem;'>
            </td>
            <td>
              <input type='text'
                    data-compare-value='<?=$detail['prd_qty_changed'] ? $detail['prd_change_qty'] : $detail['prd_order_qty']?>'
                    data-compare-target='detail[<?=$i?>][detail][prd_qty_changed]'
                    name='detail[<?=$i?>][detail][prd_change_qty]' 
                    value='<?=$detail['prd_qty_changed'] ? $detail['prd_change_qty'] : $detail['prd_order_qty']?>' 
                    style='width: 3rem;'>
            </td>
            <td>
              <label class='d-flex flex-row align-items-center justify-content-center'>
                <input type='checkbox'
                      class='value-change me-1'
                      data-compare-value='<?=$detail['order_excepted']?>'
                      data-compare-target='detail[<?=$i?>][detail][order_excepted_check]'
                      data-cancel-parent='.detail_items'
                      data-cancel-target='input[name="detail[<?=$i?>][detail][request_amount]"]'
                      data-cancel-value='0'
                      name='detail[<?=$i?>][detail][order_excepted]'
                      value='<?=$detail['order_excepted']?>'
                      <?=$detail['order_excepted'] == true ? 'checked': ''?>>
                <span>취소</span>
              </label>
            </td>
            <td>
              <?=$detail['currency_sign']?>
              <?php 
                $qty = 0; $price = 0;
                if ( $detail['order_excepted'] ) {
                  echo 0;
                } else {
                  if ( $detail['prd_qty_changed'] ) {
                    $qty = $detail['prd_change_qty'];
                  } else $qty = $detail['prd_order_qty'];

                  if ( $detail['prd_price_changed'] ) {
                    $price = $detail['prd_change_price'];
                  } else $price = $detail['prd_price'];
                }
              ?>
              <input type='text' 
                class='text-end bg-dark bg-opacity-10 request-subtotal'
                name='detail[<?=$i?>][detail][request_amount]'
                <?php if ( $detail['order_excepted'] ) : ?>
                value='0.00'
                <?php else: ?>
                value='<?=number_format( ($qty * $price), $detail['currency_float'] )?>'
                <?php endif; ?>
                data-temp='<?=number_format( ($qty * $price), $detail['currency_float'] )?>'
                style='width: 5rem;'
                readonly>
            </td>
            <td class='px-0 w-20p'>
              <div class='d-flex flex-row flex-wrap justify-content-between w-100'>
                <?php if( !empty($requirement[$i]) ) :
                  foreach ($requirement[$i] AS $j => $require ) : ?>
                  <?php if ( !$require['requirement_check'] ) : ?>
                  <div class='d-flex flex-column mx-1' style='width: 10rem;'>
                    <div class='text-start d-flex flex-row flex-nowrap justify-content-between'>
                      <p class='fw-bold' 
                            data-toggle='tooltip'
                            data-placement='top'
                            title='<?=$require['requirement_detail']?>'>
                        <?=$require['requirement_kr']?>
                      </p>
                      <!-- <label class='d-flex flex-row' style='font-size: 0.7rem;'>
                        <input class='value-change me-1' 
                              type='checkbox' 
                              name='detail[<?=$i?>][requirement][<?=$j?>][requirement_check]'
                              value='<?=$require['requirement_check']?>'
                              <?=$require['requirement_check'] == true ? 'checked' : ''?>>
                        확인요청
                      </label> -->
                    </div>
                    <textarea class='w-100' 
                          name='detail[<?=$i?>][requirement][<?=$j?>][requirement_reply]'
                          placeholder='<?=$require['requirement_detail']?>'
                          row='2'
                          style='height: 2rem;'></textarea>
                  </div>
                  <?php endif; ?>
                <?php endforeach;
                endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach;
        endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  </form>
</main>