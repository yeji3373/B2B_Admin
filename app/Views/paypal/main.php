<main>
<form method='post' class='inputLabel' action='<?=base_url('orders/paypalList')?>'>
  <div class='mb-2 border border-dark p-2 rounded-1'>
    <div class='d-flex flex-row flex-wrap'>
      <div class='d-flex flex-column me-3 w-10p'>
        <label class='mb-2'>Invoice 번호</label>
        <input type='text' name='invoice_number' maxlength='25'
              value='<?=(!empty($_POST['invoice_number']) ? $_POST['invoice_number'] : '')?>'>
      </div>
      <div class='d-flex flex-column me-3 w-10p'>
        <label class='mb-2'>Buyer email</label>
        <input type='text' name='recipient_email' value='<?=(!empty($_POST['recipient_email']) ? $_POST['recipient_email'] : '')?>'>
      </div>
      <div class='d-flex flex-column me-3 w-10p'>
        <label class='mb-2'>Invoice status</label>
        <select name='status'>
          <option value=''>전체</option>
          <?php if ( !empty($invoiceStatus) ) : 
            foreach ( $invoiceStatus AS $status ) : 
              echo "<option value={$status['value']} ";
              echo (!empty($_POST['status']) && $status['value'] == $_POST['status'] ? 'selected' : '');
              echo ">{$status['value']}</option>";
            endforeach;
          endif; ?>
        </select>
      </div>
      <!-- <div class='d-flex flex-column w-auto'>
        <label>invoice date range</label>
        <div class='d-flex flex-row'>
          <p class='me-1 mt-0 mb-0'>
            <input type='text' name='invoice_date_range[start]'/>
            <label>Start Date</label>
          </p>
          <p class='mt-0 mb-0'>
            <input type='text' name='invoice_date_range[end]'/>
            <label>End Date</label>
          </p>
        </div>
      </div> -->
    </div>
    <div class='mt-2'>
      <button class='btn btn-small btn-primary' type='submit'>검색</button>
    </div>
  </div>
</form>
<?php echo view('Paypal\Views\Invoice\NewInvoice') ?>
<table class='w-75'>
  <thead>
    <tr>
      <th>NO</th>
      <!-- <th>담당자</th> -->
      <th>인보이스 번호</th>
      <th>buyer email</th>
      <th>금액</th>
      <!-- <th>지불기일</th> -->
      <th>잔액</th>
      <th>지불현황</th>
      <!-- <th>상세보기</th> -->
      <th>발행일</th>
    </tr>
  </thead>
  <tbody>
    <?php if ( !empty($paypalList) ) : 
      foreach ( $paypalList AS $i => $list ) : 
    ?>
    <tr>
      <td><?=$i + 1?></td>
      <!-- <td><?//=$list['manager_name']?></td> -->
      <td>
        <div>
          <p><?=$list['detail']['invoice_number']?></p>
          <p>
            <!-- <a href='<?//=$list['links'][0]['href']?>'  -->
            <a href='<?=$invoiceViewrUrl.$list['id']?>'
              class='text-decoration-underline text-primary' 
              target='_blank'><?=$list['id']?></a>
          </p>
        </div>
      </td>
      <td><?=!empty($list['primary_recipients']) ? $list['primary_recipients'][0]['billing_info']['email_address'] : '' ?></td>      
      <td><?=$list['amount']['currency_code'].' '.$list['amount']['value']?></td>
      <td><?=$list['due_amount']['currency_code'].' '.$list['due_amount']['value']?></td>
      <td>
        <?=$list['status']?>
        <!-- <div class='text-bg-danger rounded-2' role='button'>확인</div> -->
      </td>
      <!-- <td>
        <p class='text-center'>
          <input type='button' class='btn btn-sm btn-primary py-1 invoiceDetail' value='버튼'/>
        </p>
      </td> -->
      <td><?=$list['detail']['invoice_date']?></td>
    </tr>
    <?php endforeach;
      endif; ?>        
  </tbody>
</table>
</main>