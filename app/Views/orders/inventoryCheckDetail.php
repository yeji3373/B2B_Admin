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
          <span><?=$order['currency_sign']. number_format($order['request_amount'], $order['currency_float'])?></span>
          / <?=$order['currency_sign']?><span class='inventory_fixed_amount'><?=number_format($order['inventory_fixed_amount'], $order['currency_float'])?></span>
        </div>
      </div>
    </div>
    <div class='mt-3 d-flex flex-column'>
      <div class='text-end mb-2 px-0 d-flex flex-row'>
        <?php if ( !empty($packagingStatus) && !empty($packaging_id) ) : ?>
        <div class='w-30 p-0 pe-2'>
          <input type='hidden' class='package' data-name='packaging[packaging_id]' value='<?=$packaging_id?>'>
          <input type='hidden' class='package packaging-order-by' data-name='packaging[order_by]'>
          <select class='form-select package packaging-status' data-name='packaging[status_id]'>
            <?php foreach ($packagingStatus AS $p => $pStatus) :?>
            <option value='<?=$pStatus['idx']?>' 
                    data-has-email='<?=$pStatus['has_email']?>'
                    data-email-id='<?=$pStatus['email_id']?>'
                    data-current-step='<?=!empty($pStatus['selected']) ? '1' : '0'?>'
                    data-order-by='<?=$pStatus['order_by']?>'
                    <?=!empty($pStatus['selected']) ? 'selected' : ''?>>
              <?=$pStatus['status_name']?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- 메일 보내기 성공했을 때만 활성화. 성공여부만 체크할 것인가... 아니면 값을 갖고 있을까도 고민하기-->
        <input type='hidden' data-name='packaging[send_email]' value='1'>
        <button class='email-send btn btn-primary d-none me-1'>메일보내기</button>
        <?php endif; ?>
        <button class='btn btn-primary'>저장</button>
      </div>
      <table>
        <thead>
          <tr>
            <th rowspan='2' style='width: 2%;'>No.</th>
            <th rowspan='2' style='width: 4%;'>브랜드</th>
            <th colspan='4' style='width: auto;'>제품정보</th>
            <th rowspan='2' style='width: 4%;'>주문수량</th>
            <th rowspan='2' style='width: 4%;'>주문취소(제외)</th>
            <th rowspan='2' style='width: 4%;'>주문/요청 총금액</th>
            <th rowspan='2' style='width: auto;'>요청사항</th>
            <th rowspan='2' style='width: 10%;'>기타</th>
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
          <tr class='detail_items <?=$detail['order_excepted'] ? 'bg-danger bg-opacity-10' : ''?>'>
            <td>
              <?=$i + 1?>
              <input type='hidden' name='detail[<?=$i?>][id]' value='<?=$detail['id']?>'>
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
              <div class='d-flex flex-column text-end'>
                <p class='<?=!empty($detail['prd_price_changed']) ? 'text-decoration-line-through' : ''?>'><?=$detail['currency_sign'].$detail['prd_price']?></p>
                <p>
                  <?=$detail['currency_sign']?>
                  <input type='number'
                        step='any'
                        data-compare-value='<?=$detail['prd_price_changed'] ? $detail['prd_change_price'] : $detail['prd_price']?>'
                        data-compare-target='detail[<?=$i?>][prd_price_changed]'
                        class='request-amount-change prd-price'
                        name='detail[<?=$i?>][prd_change_price]' 
                        value='<?=$detail['prd_price_changed'] ? $detail['prd_change_price'] : $detail['prd_price']?>' 
                        style='width: 5rem;'>
                </p>
            </td>
            <td>
              <div class='d-flex flex-column text-end'>
                <p class='<?=!empty($detail['prd_qty_changed']) ? 'text-decoration-line-through' : ''?>'><?=$detail['prd_order_qty']?></p>
                <p>
                  <input type='number'
                        step='any'
                        data-compare-value='<?=$detail['prd_qty_changed'] ? $detail['prd_change_qty'] : $detail['prd_order_qty']?>'
                        data-compare-target='detail[<?=$i?>][prd_qty_changed]'
                        class='request-amount-change prd-qty'
                        name='detail[<?=$i?>][prd_change_qty]' 
                        value='<?=$detail['prd_qty_changed'] ? $detail['prd_change_qty'] : $detail['prd_order_qty']?>' 
                        style='width: 3rem;'>
                </p>
              </div>
            </td>
            <td>
              <label class='d-flex flex-row align-items-center justify-content-center'>
                <input type='checkbox'
                      class='value-change order_excepted  me-1'
                      data-compare-value='<?=$detail['order_excepted']?>'
                      data-compare-target='detail[<?=$i?>][order_excepted_check]'
                      data-cancel-parent='.detail_items'
                      data-cancel-target='.request-subtotal'
                      data-cancel-value='0'
                      name='detail[<?=$i?>][order_excepted]'
                      value='<?=$detail['order_excepted']?>'
                      <?=$detail['order_excepted'] == true ? 'checked': ''?>>
                <span>취소</span>
              </label>
            </td>
            <td>
              <?=$detail['currency_sign']?>
              <?php 
                $qty = 0; $price = 0;
                if ( $detail['prd_qty_changed'] ) {
                  $qty = $detail['prd_change_qty'];
                } else $qty = $detail['prd_order_qty'];

                if ( $detail['prd_price_changed'] ) {
                  $price = $detail['prd_change_price'];
                } else $price = $detail['prd_price'];
              ?>
              <input type='text' 
                class='text-end bg-dark bg-opacity-10 request-subtotal'
                data-name='order[request_amount]'
                <?php if ( $detail['order_excepted'] ) : ?>
                value='0.00'
                <?php else: ?>
                value='<?=number_format( ($qty * $price), $detail['currency_float'] )?>'
                <?php endif; ?>
                data-temp='<?=number_format( ($qty * $price), $detail['currency_float'] )?>'
                style='width: 5rem;'
                readonly>
            </td>
            <td class='w-20p'>
              <div class='d-flex flex-row flex-wrap w-100 requirement-group'>
                <?php if( !empty($requirement[$i]) ) :
                  foreach ($requirement[$i] AS $j => $require ) : ?>
                  <?php if ( !$require['requirement_check'] ) : ?>
                  <div class='d-flex flex-column w-30p'>
                    <div class='text-start d-flex flex-row flex-nowrap justify-content-between'>
                      <p class='fw-bold' 
                            data-toggle='tooltip'
                            data-placement='top'
                            title='<?=$require['requirement_detail']?>'>
                        <?=$require['requirement_kr']?>
                      </p>
                    </div>
                    <input type='hidden' name='requirement[<?=$j?>][idx]' value='<?=$require['idx']?>'>
                    <textarea class='w-100' 
                          name='requirement[<?=$j?>][requirement_reply]'
                          placeholder='<?=$require['requirement_detail']?>'
                          row='2'
                          style='height: 2rem;'><?=$require['requirement_reply']?></textarea>
                  </div>
                  <?php endif; ?>
                <?php endforeach;
                endif; ?>
              </div>
            </td>
            <td>
              <textarea class='w-100'
                  name='detail[<?=$i?>][detail_desc]'
                  style='height: 2rem;'
                  row='2'><?=$detail['detail_desc']?></textarea>
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