<main class='position-rel0ative'>
  <title>재고 확인 / 주문 요청 상세</title>
  <div class='order-detail-container inventory-detail-container border-0'>
    <?php 
    $pay_step = 0;
    if ( !empty($packagingStatus) ) :
      if ( isset($packagingStatus[0]['pay_step']) ) : 
        if ( $packagingStatus[0]['pay_step'] > 0 ) : 
          $pay_step = $packagingStatus[0]['pay_step'];
        endif;
      endif;
    endif;
    ?>
    <input type='hidden' id='pay-step' value='<?=$pay_step?>'>
    <?php if ( !empty($order)) : ?> 
    <!-- <?php var_dump($order) ?> -->
    <div class='d-grid grid-three border'>
      <div class='d-flex flex-column border border-0 border-end'>
        <label class='border border-0 border-bottom'>업체(buyer)명</label>
        <div class='con'>
          <?=$order['buyer_name']?> 
          <?php if ( !empty($order['user_email']) ) :
              echo mailto($order['user_email'], " ({$order['user_name']})");
            else :
              echo "{$order['user_name']}";
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
    </div>
    <div class='d-grid grid-quad border mt-2'>
      <div class='d-flex flex-column border-end'>
        <label class='border border-0 border-bottom'>재고요청</label>
        <div class='con'>
          <span><?=$order['currency_sign']. number_format($order['request_amount'], $order['currency_float'])?></span>
        </div>
      </div>
      <div class='d-flex flex-column border-end'>
        <label class='border border-0 border-bottom'>재고확정</label>
        <div class='con'>
        <?=$order['currency_sign']?><span class='inventory_fixed_amount'><?=number_format($order['inventory_fixed_amount'], $order['currency_float'])?></span>
        </div>
      </div>
      <div class='d-flex flex-column border-end'>
        <label class='border border-0 border-bottom'>주문확정금액</label>
        <div class='con'>
          <?=$order['currency_sign']?><span class='fixed_amount'><?=number_format($order['order_amount'], $order['currency_float'])?></span>
        </div>
      </div>
      <div class='d-flex flex-column'>
        <label class='border border-0 border-bottom'>최종확정금액</label>
        <div class='con'>
          <?=$order['currency_sign']?><span class='decide_amount'><?=number_format($order['decide_amount'], $order['currency_float'])?></span>
        </div>
      </div>
    </div>
    <?php if ( !empty($order['receipt_type'])) { ?>
    <div class='w-100 mt-2 d-flex flex-column'>
      <p class='fw-bold'>Invoice List</p>
      <?=view('orders/includes/invoiceDelivery')?>
    </div>
    <?php } ?>
    <?php endif; ?>
    <form method='post' action='<?=site_url('orders/inventoryEdit')?>'>
    <?php if ( !empty($order)) : ?>
    <input type='hidden' name='order[id]' value='<?=$order['id']?>'>
    <input type='hidden' name='order[inventory_fixed_amount]' value='<?=$order['inventory_fixed_amount']?>'>
    <input type='hidden' name='order[fixed_amount]' value='<?=$order['fixed_amount']?>'>
    <input type='hidden' name='order[decide_amount]' value='<?=$order['decide_amount']?>'>
    <?php endif; ?>
    <div class='mt-3 d-flex flex-column'>
      <div class='text-end mb-2 px-0 d-flex flex-row'>
        <?php if ( !empty($packagingStatus) && !empty($packaging_id) ) :
          //print_r($packagingStatus); ?>
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
                    data-disabled='<?=is_null($pStatus['department_ids']) ? '0' : (($pStatus['department_ids'] == -1) ? 1 : 0);?>'
                    data-order-fix='<?=$pStatus['payment_request']?>'
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
        <button class='btn btn-primary status-save-btn'>저장</button>
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
          foreach ($details AS $i => $detail ) :
            $seletedOptions = []; 
            $canceled = 0;
            $disabled = NULL;
            $qty = 0;
            $price = $detail['prd_price'];

            if ( !empty($detail['order_excepted']) ) :
              $canceled = 1;
              $disabled = ' disabled';
            else :
              if ( isset($detail['cancele_request']) && !empty($detail['cancele_request'])) {
                $canceled = 1;
                $disabled = ' disabled';
              }
            endif;
          
          ?>
          <tr class='detail_items <?=$canceled ? 'bg-danger bg-opacity-10' : ''?>'>
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
                <p class='<?=$pay_step > 0 && (!empty($detail['prd_change_price']) && $detail['prd_change_price'] != $detail['prd_price'])? 'text-decoration-line-through' : ''?>'>
                  <?=$detail['currency_sign'].number_format($detail['prd_price'], 2)?>
                </p>
                <?php if ( $pay_step > 0 ) : 
                  $price = !empty($detail['prd_change_price']) ? $detail['prd_change_price'] : $detail['prd_price'];
                ?>
                <p>
                  <?=$detail['currency_sign']?>
                  <input type='number' step='any' min='1'
                        data-compare-value='<?=!empty($detail['prd_change_price']) ? $detail['prd_change_price'] : $detail['prd_price']?>'
                        data-compare-target='detail[<?=$i?>][prd_price_changed]'
                        class='request-amount-change prd-price'
                        name='detail[<?=$i?>][prd_change_price]' 
                        value='<?=!empty($detail['prd_change_price']) ? $detail['prd_change_price'] : $detail['prd_price']?>'
                        <?=$disabled?>>
                </p>
                <?php endif; ?>
            </td>
            <td>
              <div class='d-flex flex-column text-end'>
                <p class='<?=$pay_step > 0 && (!empty($detail['prd_change_qty']) && $detail['prd_change_qty'] != $detail['prd_order_qty']) ? 'text-decoration-line-through' : ''?>'>
                  <?=$detail['prd_order_qty']?>
                </p>
                <!-- step 1 -->
                <?php if ( $pay_step >= 1 ) : ?>
                <p>
                  <?php if ( $pay_step > 1 ) : ?>
                    <?=!empty($detail['prd_change_qty']) ? $detail['prd_change_qty'] : $detail['prd_order_qty']?>
                  <?php else : ?>
                  <input type='number'
                        step='any'
                        min='1'
                        class='request-amount-change prd-qty'
                        name='detail[<?=$i?>][prd_change_qty]' 
                        value='<?=!empty($detail['prd_change_qty']) ? $detail['prd_change_qty'] : $detail['prd_order_qty']?>' 
                        <?=$disabled?>>
                  <?php endif; ?>
                </p>
                <?php endif ; ?>
                <!-- step 1 -->
                
                <!-- step 2 -->
                <?php if ( $pay_step >= 2 ) :
                  $third_qty = 0;
                  if ( empty($detail['prd_fixed_qty']) ) {
                    if ( empty($detail['prd_change_qty']) ) {
                      $third_qty = $detail['prd_order_qty'];
                    } else $third_qty = $detail['prd_change_qty'];
                  } else {
                    $third_qty = $detail['prd_fixed_qty'];
                  } 
                  $qty = $third_qty;
                ?>
                <p>
                  <?php if ( $pay_step > 2 ) : ?>
                    <span><?=number_format($third_qty);?></span>
                  <?php else : ?>
                    <input type='number'
                          step='any'
                          class='request-amount-change prd-qty fixed-qty'
                          name='detail[<?=$i?>][prd_fixed_qty]'
                          value='<?=$third_qty?>'
                          <?=$disabled?>>
                  <?php endif;?>
                </p>
                <?php endif; ?>
                <!-- step 2 -->

                <!-- step 3 -->
                <?php if ( $pay_step >= 3 ) : 
                  $fourth_qty = 0;
                  if ( empty($detail['prd_final_qty']) ) {
                    $fourth_qty = $third_qty;
                  } else {
                    $fourth_qty = $detail['prd_final_qty'];
                  } 
                  $qty = $fourth_qty;
                ?>
                <p>
                  <?php if ( $pay_step > 3 ) : ?>
                    <span><?=number_format($fourth_qty)?></span>
                  <?php else : ?>
                    <input type='number'
                          step='any'
                          class='request-amount-change prd-qty final_qty'
                          name='detail[<?=$i?>][prd_final_qty]'
                          value='<?=$fourth_qty?>'
                          <?=$disabled?>>
                  <?php endif; ?>
                </p>
                <?php endif; ?>
                <!-- step 3 -->
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
                      value='<?=$canceled?>'
                      <?= $canceled ? 'checked': ''?>>
                <span>취소</span>
              </label>
            </td>
            <td>
              <?=$detail['currency_sign']?>
              <?php
                $tempQty = $qty;
                if ( $canceled ) {
                  $qty = 0;
                }
              ?>
              <input type='hidden' name='order[product_total_amount][<?=$i?>][id]' value='<?=$detail['id']?>'>
              <input type='text' 
                class='text-end bg-dark bg-opacity-10 request-subtotal'
                data-name='order[request_amount]'
                name='order[product_total_amount][<?=$i?>][total]'
                value='<?=number_format( ($qty * $price), $detail['currency_float'] )?>'
                data-temp='<?=number_format( ($tempQty * $price), $detail['currency_float'] )?>'
                readonly>
            </td>
            <td class='w-20p'>
              <div class='d-flex flex-column flex-wrap w-100 requirement-group'>
                <?php if( !empty($detail['requirement']) ) :
                  foreach ($detail['requirement'] AS $j => $require ) : ?>
                  <div class='d-flex flex-column requirement-item w-100'>
                    <div class='d-flex flex-row w-100'>
                      <div class='text-start w-30p d-flex flex-row flex-nowrap justify-content-between'>
                        <p class='fw-bold' 
                              data-toggle='tooltip'
                              data-placement='top'
                              title='<?=$require['requirement_detail']?>'>
                          <?=$require['requirement_kr']?>
                        </p>
                      </div>
                      <div class='w-70p'>
                        <input type='hidden' class='requirement_option_ids' 
                              name='requirement[<?=$i?>][<?=$j?>][requirement_option_ids]' 
                              value='<?=!empty($require['requirement_option_ids']) ? $require['requirement_option_ids'] : ''?>'>
                        <input type='hidden' name='requirement[<?=$i?>][<?=$j?>][idx]' value='<?=$require['idx']?>'>
                        <textarea class='w-100' 
                              name='requirement[<?=$i?>][<?=$j?>][requirement_reply]'
                              placeholder='<?=$require['requirement_detail']?>'
                              row='2'
                              style='height: 2rem;'
                              <?php if((!empty($option_disabled)) && ($option_disabled == 1)) {
                                      echo " disabled";
                                    }?>
                              ><?=$require['requirement_reply']?></textarea>
                      </div>
                    </div>
                    <div class='d-flex flex-column w-100 text-start'>
                      <?php if ( empty($require['requirement_check']) ) :
                        if ( !empty($requirementOption) ) :
                          $checkedOption = [];
                          if ( !empty($require['requirement_option_ids']) ) :
                            $checkedOption = explode(",", $require['requirement_option_ids']);
                          endif;
                        foreach( $requirementOption AS $rOption ) :
                            if ( $rOption['requirement_idx'] == $require['requirement_id'] ) :
                              echo "<label ";
                              if($require['requirement_selected_option_id'] == $rOption['idx']){
                                echo "class='text-bg-secondary'";
                              }
                              echo ">
                                      <input type='checkbox' 
                                        class='require-option-use'
                                        value='{$rOption['idx']}' 
                                        data-add-target='.requirement_option_ids'";
                              if ( !empty($checkedOption) && in_array($rOption['idx'], $checkedOption) ) {
                                echo " checked";
                              }
                              if ( !empty($option_disabled) || !empty($price_disabled) ) echo " disabled='true'";
                              echo  ">{$rOption['option_name']}
                                    </label>";
                            endif;
                          endforeach;
                        endif;
                      endif; ?>
                    </div>
                  </div>
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
  <div class='invoice-edit position-fixed top-0 start-0 d-none w-100 bg-dark bg-opacity-25 overflow-auto h-100'>
</main>