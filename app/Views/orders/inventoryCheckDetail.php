<main class='position-rel0ative'>
  <title>재고 확인 요청 상세</title>
  <form method='post' action='<?=site_url('orders/inventoryEdit')?>'>
  <div class='order-detail-container inventory-detail-container border-0'>
    <div class='d-grid grid-quad border'>
      <div class='d-flex flex-column border border-0 border-end'>
        <label class='border border-0 border-bottom'>업체(buyer)명</label>
        <div class='con'>
          <?=$order['buyer_name']?> 
          <?php 
          // if ( !empty($order['user_email']) ) :
            // echo mailto($order['user_email'], $order['user_id']."(".$order['user_name'].")");
          // else :
            // echo "{$order['user_id']}({$order['user_name']})";
          // endif;
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
        <label class='border border-0 border-bottom'>재고요청 / 재고확정 / 주문확정금액</label>
        <div class='con'>
          <input type='hidden' name='order[id]' value='<?=$order['id']?>'>
          <input type='hidden' name='order[request_amount]' value='<?=$order['request_amount']?>'>
          <input type='hidden' name='order[order_fix]' value='0'>
          <input type='hidden' name='order[inventory_fixed_amount]' value='<?=$order['inventory_fixed_amount']?>'>
          <input type='hidden' name='order[order_amount]' value='<?=$order['order_amount']?>'>
          <span><?=$order['currency_sign']. number_format($order['request_amount'], $order['currency_float'])?></span>
          / <?=$order['currency_sign']?><span class='inventory_fixed_amount'><?=number_format($order['inventory_fixed_amount'], $order['currency_float'])?></span>
          / <?=$order['currency_sign']?><span class='order_amount'><?=number_format($order['order_amount'], $order['currency_float'])?></span>
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
          foreach ($details AS $i => $detail ) :?>
          <?php $seletedOptions = []; 
                $canceled = 0; ?>
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
                        style='width: 5rem;'
                        <?=(!empty($price_disabled) && ($price_disabled == 1)) ? 'disabled' : ''?>
                        >
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
                        style='width: 3rem;'
                        <?=(!empty($price_disabled) && ($price_disabled == 1)) ? 'disabled' : ''?>
                        >
                </p>
                <p>
                <?php $value = 0;
                      if(empty($detail['prd_fixed_qty'])) {
                        if(!empty($requirement[$i])) : 
                          foreach($requirement[$i] AS $j => $require) : 
                            if(!empty($require['requirement_selected_option_id'])) : 
                              array_push($seletedOptions, $require['requirement_selected_option_id']);
                            endif; 
                          endforeach;
                          if(!empty($seletedOptions)) :
                            if(in_array('3', $seletedOptions) || in_array('4', $seletedOptions)) :
                              $value = 0;
                              $canceled = 1;
                            else :
                              if(in_array('1', $seletedOptions)) :
                                $value = $detail['prd_change_qty'];
                              else :
                                $value = $detail['prd_order_qty'];
                              endif;
                            endif;
                          else :
                            if(empty($detail['prd_qty_changed'])) {
                              $value = $detail['prd_order_qty'];
                            } else {
                              $value = $detail['prd_change_qty'];
                            }
                          endif;
                        else :
                          if(empty($detail['prd_qty_changed'])) {
                            $value = $detail['prd_order_qty'];
                          } else {
                            $value = $detail['prd_change_qty'];
                          }
                        endif; 
                      }else{
                        $value = $detail['prd_fixed_qty'];
                      }
                      echo "<input type='number' class='fixed-qty' name='detail[{$i}][prd_fixed_qty]' value='".$value."' style='width: 3rem;'";
                            if(!empty($order['order_fixed']) && $order['order_fixed'] == 1){
                              echo " disabled='true'";
                            }
                      echo ">";
                      ?>
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
                      <?php 
                      $excepted = NULL;
                      if($detail['order_excepted'] == 0) :
                        if($canceled == 1) :
                          $excepted = 1;
                        else :
                          $excepted = 0;
                        endif;
                      else :
                        $excepted = 1;
                      endif;
                      if(!empty($order['order_fixed']) && $order['order_fixed'] == 1) {
                        echo " disabled = 'true'";
                      }
                      ?>
                      value='<?=$excepted?>'
                      <?=($detail['order_excepted'] == true || $canceled == 1) ? 'checked': ''?>>
                <span>취소</span>
              </label>
            </td>
            <td>
              <?=$detail['currency_sign']?>
              <?php 
                $qty = 0; $price = 0;
                if(empty($detail['prd_fixed_qty'])) {
                  if ( $detail['prd_qty_changed'] ) {
                    if(!empty($seletedOptions)) :
                      if(in_array('1', $seletedOptions)) :
                        $qty = $detail['prd_change_qty'];
                      else :
                        $qty = $detail['prd_order_qty'];
                      endif;
                    else :
                      $qty = $detail['prd_change_qty'];
                    endif;
                  } else $qty = $detail['prd_order_qty'];

                  if ( $detail['prd_price_changed'] ) {
                    $price = $detail['prd_change_price'];
                  } else $price = $detail['prd_price'];
                } else {
                  $qty = $detail['prd_fixed_qty'];
                  if ( $detail['prd_price_changed'] ) {
                    $price = $detail['prd_change_price'];
                  } else $price = $detail['prd_price'];
                }
              ?>
              <input type='hidden' name='order[product_total_amount][<?=$i?>][id]' value='<?=$detail['id']?>'>
              <input type='text' 
                class='text-end bg-dark bg-opacity-10 request-subtotal'
                data-name='order[request_amount]'
                name='order[product_total_amount][<?=$i?>][total]'
                <?php if ( $detail['order_excepted'] || $canceled == 1) : ?>
                value='0.00'
                <?php else: ?>
                value='<?=number_format( ($qty * $price), $detail['currency_float'] )?>'
                <?php endif; ?>
                data-temp='<?=number_format( ($qty * $price), $detail['currency_float'] )?>'
                style='width: 5rem;'
                readonly>
            </td>
            <td class='w-20p'>
              <div class='d-flex flex-column flex-wrap w-100 requirement-group'>
                <?php if( !empty($requirement[$i]) ) :
                  foreach ($requirement[$i] AS $j => $require ) : ?>
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
                                      echo " disabled='true'";
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
                              if ( !empty($checkedOption) ) {
                                if ( in_array($rOption['idx'], $checkedOption) ) {
                                  echo " checked='true'";
                                }
                              }
                              if((!empty($option_disabled)) && ($option_disabled == 1)) { 
                                echo " disabled='true'";
                              }
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
</main>