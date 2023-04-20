<form method='post' class='inputLabel' action='<?=base_url('orders/paypal')?>'>
  <div class='my-3'>
    <div class='d-flex flex-row justify-content-between'>
      <h2>새 인보이스</h2>
      <button type='sumbit' class='btn btn-small btn-primary'>보내기</button>
    </div>
    <div class='d-flex flex-row'>
      <div class='d-flex flex-column border border-dark p-3 w-60p me-auto'>
        <div class='d-flex flex-column'>
          <h4 class='fs-6'>청구 대상 : </h4>
          <p class='w-100 mt-0'>
            <input type='hidden' name='billing_info[additional_info_value]' value="add-info">
            <input type='email' class='bg-white' name='billing_info[email_address]' value required>
            <label class='bg-white'>이메일주소</label>            
          </p>
        </div>
        <div class='d-flex flex-column mt-2'>
          <label class='fs-6'>상품</label>
          <div class='d-flex flex-column item-container'>
            <div class='d-flex flex-row border border-secondary rounded-2 position-relative item-group'>
              <p class='mx-2'>
                <input type='text' class='bg-white' name='items[0][name]' aria-init='' aria-name='name' required />
                <label class='bg-white'>상품이름</label>
              </p>
              <p class='mx-0 me-2'>
                <input type='text' class='bg-white' name='items[0][quantity]' aria-init='1' aria-name='quantity' value='1' required />
                <label class='bg-white'>수량</label>
              </p>
              <p class='mx-0 me-2'>
                <input type='text' class='me-2 valided' name='currency_code' aria-init='USD' value='USD' readonly />
                <label>통화</label>
              </p>
              <p class='mx-0'>
                <input type='text' class='bg-white' name='items[0][unit_amount]' aria-init='' aria-name='unit_amount' required />
                <label class='bg-white'>가격</label>
              </p>
            </div>
          </div>
          <div class='add_items text-primary text-end fw-bold text-decoration-underline' role='button'>+ 상품 또는 서비스 추가</div>
        </div>
      </div>
      <div class='d-flex flex-column border border-dark p-3 w-35p partial_payment_check'>
        <p class='w-100 mb-0'>
          <input type='text' class='bg-white' name='invoice_number' value required />
          <label class='bg-white'>인보이스 번호</label>
        </p>
        <p class='w-100 mb-0'>
          <input type='text' class='bg-white valided' name='invoice_date' value required />
          <label class='bg-white'>인보이스 날짜</label>
        </p>
        <p class='w-100 mb-0'>
          <select name='term_type' class='bg-white'>
            <option value="DUE_ON_RECEIPT" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>수취 시</option>
            <option value="NO_DUE_DATE" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>지불 기일 없음</option>
            <option value="DUE_ON_DATE_SPECIFIED" aria-hidden='false' aria-disabled='false' aria-target='input[name=due_date]'>특정 날짜에</option>
            <option value="NET_10" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>10일 후</option>
            <option value="NET_15" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>15일 후</option>
            <option value="NET_30" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>30일 후</option>
            <option value="NET_45" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>45일 후</option>
            <option value="NET_60" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>60일 후</option>
            <option value="NET_90" aria-hidden='true' aria-disabled='true' aria-target='input[name=due_date]'>90일 후</option>
          </select>
          <label class='bg-white'>지불기일</label>
        </p>
        <p class='w-100 my-0'>
          <input type='text' name='due_date' class='bg-white d-none' disabled>
        </p>
        <p class='w-100 mb-0'>
          <label class='fs-6'>
            <input type='checkbox' name='partial_payment' 
              class='form-check-input value-change'
              data-find-parent='div.partial_payment_check'
              data-find-target='input[name=minimum_amount]'
              data-condition='[ {"condition": "0", "action": "disabled", "value": true}
                              , {"condition": "1", "action": "disabled", "value": false}
                              , {"condition": "0", "action": "required", "value": false}
                              , {"condition": "1", "action": "required", "value": true}]'
              />
            부분 결제 허용
          </label>
        </p>
        <p class='w-100 my-0'>
          <input type='text' class='bg-white' name='minimum_amount' minlength='1' disabled />
          <label class='bg-white'>최소 결제 금액</label>
        </p>
      </div>
    </div>
  </div>
</form>