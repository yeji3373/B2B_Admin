<?php if ( !empty($receipts) && !empty($order)) : 
  $isPaypal = false;
  if ($order['payment'] == 'Paypal') $isPaypal = true; ?>
<div class='invoice-delivery-edit-container pi-edit-container p-0'>
  <div class='header-container'>
    <div class='table-thead table-row text-center fw-bold'>
      <div class='table-thead-th table-cell border-bottom-0 border-end border-dark w-4p'>No</div>
      <div class='table-thead-th table-cell border-bottom-0 border-end border-dark w-5p'>결제비율</div>
      <div class='table-thead-th table-cell border-bottom-0 border-end border-dark w-15p p-0'>
        <div class='border-bottom border-dark w-100 p-1'>
          결제금액<?=!empty($order['currency_code']) ? "({$order['currency_code']})" : ""?>
        </div>
        <div class='d-flex flex-row w-100 fs-7'>
          <div class='table-cell border-end border-dark w-50 p-1'>상품가격</div>
          <div class='table-cell item-end w-50 p-1'>남은금액</div>
        </div>
      </div>
      <div class='table-thead-th table-cell border-bottom-0 border-end border-dark w-5p'>결제현황</div>
      <div class='table-thead-th table-cell border-bottom-0 border-end border-dark w-25 p-0'>
        <div class='border-bottom border-dark w-100 p-1'>결제정보</div>
        <div class='d-flex flex-ro w-100 fs-7'>
          <div class='table-cell border-end border-dark w-25 p-1'>결제수단</div>
          <div class='table-cell item-end w-75 p-1'>상세정보</div>
        </div>
      </div>
      <div class='table-thead-th table-cell border-bottom-0 border-end border-dark w-3p'>표시</div>
      <div class='table-thead-th table-cell border-bottom-0 border-end border-dark w-27 p-0'>
        <div class='border-bottom border-dark w-100 p-1'>배송관리</div>
        <div class='d-flex flex-row w-100 fs-7'>
          <div class='table-cell border-end border-dark w-8p p-1'>적용</div>
          <div class='table-cell border-end border-dark w-28p p-1'>배송사</div>
          <div class='table-cell border-end border-dark w-50 p-1'>배송비</div>
          <div class='table-cell item-end w-14p p-1'>Forward</div>
        </div>
      </div>
      <div class='table-thead-th table-cell border-bottom-0'></div>
    </div>
  </div>
  <div class='body-container'>
    <?php if ( !empty($receipts) ) : 
    foreach($receipts as $receipt_idx => $receipt) : ?>
    <form action="<?=base_url('orders/pInvoice')?>" method="post" accept-charset="utf-8" class='p-0 m-0 table-row pi-form'>
    <input type='hidden' name='amount_paid' value='<?=$order['amount_paid']?>'>
    <input type='hidden' name='order_amount' value='<?=$order['order_amount']?>'>
    <input type='hidden' name='order_id' value='<?=$order['id']?>'>
    <input type='hidden' name='payment_status' value='<?=$receipt['payment_status']?>'>
    <input type='hidden' name='payment_id' value='<?=$order['payment_id']?>'>
    <input type='hidden' name='piControllType' value>
    <input type='hidden' name='currency_code' value='<?=$order['currency_code']?>'>
    <input type='hidden' name='receipt_type' value='<?=$receipt['receipt_type']?>'>
    <!-- <div class='table-row'> -->
      <div class='table-cell p-1 text-center border-top border-end border-dark'>
        <input type='hidden' name='receipt[receipt_id]' value='<?=$receipt['receipt_id']?>'>
        <input type='hidden' name='receipt[payment_invoce_id]' value='<?=$receipt['payment_invoice_id']?>'>
        <?=$receipt['receipt_type']?>차
      </div>
      <div class='table-cell p-1 text-center border-top border-end border-dark'>
        <select name='receipt[rq_percent]' 
          <?php if ( isset($receipt['payment_status']) && $receipt['payment_status'] != 0 ) echo " disabled";?> >
          <!-- <option>-</option> -->
          <?php for($i = 10; $i <= 100; $i += 5) { ?>
          <option value='<?=$i / 100?>' 
            <?=($receipt['rq_percent'] * 100) == $i ? 'selected' : '' ?>>
            <?=$i?>
          </option>
          <?php } ?>
        </select>%
      </div>
      <div class='table-cell text-end border-top border-end border-dark p-0 align-top'>
        <div class='d-table w-100 h-100'>
          <div class='table-cell border-end border-dark w-50 p-1'>
            <div class='w-100'>
              <?=$order['currency_sign']?>
              <input type='hidden' name='receipt[rq_amount]' value='<?=$receipt['rq_amount']?>' class='w-80p'>
              <span class='receipt-rq-amount btn btn-sm' data-name='receipt[rq_amount]'>
                <?=number_format(($receipt['rq_amount']), $order['currency_float'])?>
              </span>
            </div>
          </div>
          <div class='table-cell w-50 p-1'>
            <div class='w-100'>
              <?=$order['currency_sign']?>
              <input type='hidden' name='receipt[due_amount]' value='<?=$receipt['due_amount']?>'>
              <span class='receipt-due-amount' data-name='receipt[due_amount]'>
                <?=number_format($receipt['due_amount'], $order['currency_float'])?>
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class='table-cell text-center border-top border-end border-dark p-1'>
        <?php if ( $receipt['payment_status'] == -1 ) : 
          echo "오류";
        else :
          echo esc($status->paymentStatus[$receipt['payment_status']] );
        endif; ?>
      </div>
      <div class='table-cell border-top border-end border-dark p-0'>
        <div class='d-table w-100 h-100'>
          <div class='table-cell text-center border-end border-dark w-25 p-1'>
            <?=esc($order['payment'])?>
          </div>
          <div class='table-cell w-75'>
            <?php if ( $isPaypal ) : ?>
              <a class='btn-link' href='<?=$receipt['payment_url']?>' target='_blank'><?=esc($receipt['payment_invoice_id'])?></a>
            <?php else : ?>
              <?php 
              $checked = NULL;
              $disabled = NULL;
              if ( $receipt['payment_status'] == 100 ) : 
                $checked = ' checked';
                $disabled = ' disabled';
              endif; ?>
              <div class='register-deposit-container d-table'>
                <div class='table-cell w-50 p-1'>
                  <div class='table-row'>
                    <input type='text' name='receipt[payment_date]' placeholder='입금날짜 등록' value='<?=!empty($receipt['payment_date']) ? $receipt['payment_date'] : '' ?>' <?=$checked.$disabled?>>
                  </div>
                </div>
                <div class='table-cell w-50 p-1'>
                  <div class='table-row'>
                    <label class='d-flex flex-row align-items-center'>
                      <input type='checkbox' class='me-1' name='receipt[payment_status]' value='<?=$receipt['payment_status'] == 0 ? 100 : 0 ?>' <?=$checked.$disabled?>>입금완료
                    </label>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class='table-cell text-center border-top border-end border-dark p-1'>
        <input type='checkbox' name='receipt[display]' 
          <?php if ( !empty($receipt['display']) ) : 
            echo "checked";
          endif; ?>
          <?php if ( !empty($receipt['receipt_type']) ) :
            if ($receipt['receipt_type'] == 1) : 
              echo "disabled";
            else :
              if ( $receipt['payment_status'] != 0 ) :
                echo "disabled";
              endif;
            endif;  
          endif; ?>
          >
      </div>
      <div class='table-cell border-top border-end border-dark p-0'>
        <div class='d-table w-100 h-100'>
          <div class='table-cell text-center border-end border-dark w-8p p-1'>
            <?php if ( !empty($receipt['delivery_id']) ) : ?>
            <input type='hidden' name='delivery[id]' value='<?=$receipt['delivery_id']?>'>
            <?php if ( !empty($order) && isset($order['id']) ) { ?>
            <input type='hidden' name='delivery[order_id]' value='<?=$order['id']?>'>
            <?php } ?>
            <?php endif; ?>
            <input type='checkbox' class='delivery_apply_check' 
              data-disabled-target='.delivery_option'
              value='<?=!empty($receipt['delivery_id']) ? 1 : 0?>'
              <?=!empty($receipt['delivery_id']) ? 'checked' : '';?>
              <?=isset($receipt['payment_status']) && $receipt['payment_status'] != 0 ? 'disabled' : ''?>>
          </div>
          <div class='table-cell border-end border-dark w-28p p-1'>
            <select class='w-100 required delivery_option' name='delivery[shipment_id]'
              <?=isset($receipt['payment_status']) && $receipt['payment_status'] != 0 ? "disabled" : ''?>>
              <option value=''>-</option>
              <?php if ( !empty($shipments) ) : 
              foreach($shipments as $shipment) : ?>
              <option value='<?=$shipment['id']?>' 
                <?php if (isset($receipt['shipment_id']) && !empty($receipt['shipment_id']) ) : 
                  if ( $receipt['shipment_id'] == $shipment['id'] ) : 
                    echo "selected";
                  endif; 
                endif;?>>
                <?=$shipment['shipment_name_en']?>
              </option>
              <?php endforeach;
              endif; ?>
            </select>
          </div>
          <div class='table-cell border-end border-dark w-50 p-1'>
            <div class='d-flex flex-row justify-content-between w-100'>
              <select class='w-auto delivery_option required' name='delivery[delivery_currency_idx]'
                <?=empty($receipt['delivery_id']) ? 'disabled' : '';?>
                required>
                <option value=''>-</option>
                <?php if ( !empty($currency) ) : 
                  foreach($currency AS $c) : ?>
                  <option value='<?=$c['idx']?>' 
                    <?php if (isset($receipt['delivery_currency_idx']) && !empty($receipt['delivery_currency_idx']) ) : 
                      if ( $receipt['delivery_currency_idx'] == $c['idx'] ) : 
                        echo "selected";
                      endif; 
                    endif;?>>
                    <?=$c['currency_code']?>
                  </option>
                <?php endforeach;
                endif; ?>
              </select>
              <input class='ms-1 w-100 required delivery_option' type='text' name='delivery[delivery_price]'
                placeholder='1234.05'
                value='<?=!empty($receipt['delivery_id']) && !empty($receipt['delivery_price']) ? $receipt['delivery_price'] : ''?>'
                pattern='[0-9]+([\.][0-9]{0,2})?'
                <?=empty($receipt['delivery_id']) ? 'disabled' : '';?>
                required>
            </div>
          </div>
          <div class='table-cell text-center w-14p p-1'>
            <input type='checkbox' 
                  class='forward delivery_option' 
                  name='delivery[forward]'
                  <?=empty($receipt['delivery_id']) ? 'disabled' : '';?>
                  <?php if (isset($receipt['forward']) && !empty($receipt['forward']) ) : 
                    if ( $receipt['forward'] ) : 
                      echo "checked";
                    endif; 
                  endif;?>>
          </div>
        </div>
      </div>
      <div class='table-cell text-center border-top border-dark p-1'>
        <?php if ( $isPaypal && $receipt['payment_status'] == 0 ) : ?>
        <button class='btn btn-sm btn-secondary btn-pi payment_status_check' data-type=''>결제현황 확인</button>
        <?php endif ?>
        <?php if ( $receipt['payment_status'] == 0 ) : ?>
        <button class='btn btn-sm btn-secondary btn-pi' data-type='edit'>수정</button>
        <button class='btn btn-sm btn-secondary btn-pi' data-type='cancel'>취소</button>        
        <?php endif; ?>
        <?php if ( $receipt['payment_status'] == -1 ) : ?>
          <input type='hidden' name='receipt[receipt_id]' value='<?=$receipt['receipt_id']?>'>
          <button class='btn btn-sm btn-secondary btn-pi' data-type='receipt'>
            <?=$receipt['receipt_type']?>차 재발행
          </button>
        <?php endif; ?>
        <?php if ( $receipt['payment_status'] == 100 ) : ?>
          <?php if ( $receipt['due_amount'] > 0 && ($receipt_idx + 1) >= count($receipts) ) : ?>
          <input type='hidden' name='request_amount' value='<?=$receipt['due_amount']?>'>
          <button class='btn btn-sm btn-secondary btn-pi' data-type='receipt'>
            <?=($receipt['receipt_type'] + 1)?>차 발행
          </button>
          <?php endif; ?>
          <button class='btn btn-sm btn-secondary btn-pi' data-type='refund'>환불</button>
          <?php if ( $receipt['due_amount'] == 0 && $order['complete_payment'] == 1) : ?>
          <!-- <div class='btn btn-sm btn-secondary btn-pi' data-type='ci'>CI 발행</div> -->
          <?php endif;?>
        <?php endif; ?>
      </div>
    <!-- </div> -->
    </form>
    <?php endforeach;
    endif ?>
  </div>
</div>
<?php endif; ?>