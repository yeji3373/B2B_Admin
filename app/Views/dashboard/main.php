<main id='dashboard'>
  <section>
    <h6>거래처</h6>
    <div class='text-end'>
      <a class='btn-link' href='/buyer/list?confirmation=0&dateYn=1'>전체보기</a>
    </div>
    <div>
      <p>등록대기 업체 <span class='parenthesis'><?=count($buyers)?></span></p>
      <?=view('buyer/mList')?>
    </div>
  </section>
  <section>
    <h6>주문</h6>
    <?php if ( !empty($default_currency) ) : ?>
    <form method='post' action='<?=base_url('home/currency')?>'>
    <div class='d-flex flex-column mb-2'>
      <label class='me-2'>환율 설정</label>
      <div class='d-flex flex-row align-items-baseline'>
        <input type='hidden' name='currency_idx' value=<?=$default_currency['idx']?>>
        <select class='form-select form-select-sm w-25 me-2' name='cRate_idx'>
          <?php if ( !empty($currencies) ) : 
            foreach($currencies AS $currency) : ?>
          <option value=<?=$currency['cRate_idx']?> 
              data-currencyidx='<?=$currency['currency_idx']?>'
              data-exchangerate='<?=$currency['exchange_rate']?>'
            <?=$currency['currency_idx'] == $default_currency['idx'] ? 'selected' : '' ?>>
            <?=$currency['currency_code']?>
          </option>
          <?php endforeach;
            endif; ?>
        </select>
        <input type='text' name='exchange_rate' class='form-control form-control-sm w-25' value=<?=$default_currency['exchange_rate']?>>원
      </div>
      <button class='btn btn-sm btn-dark exchange_rate_edit_btn w-25 mt-2'>변경</button>
    </form>
    </div>
    <?php endif?>

    <div class='mt-2'>
      <label class='fw-bold'>주문내역</label>
      <div class='text-end mb-1'>
        <a class='btn-link' href='<?=base_url('orders')?>'>전체보기</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>주문번호</th>
            <th>재고요청금액</th>
            <th>주문(확정)금액</th>
            <th>상태</th>
            <th>기타</th>
          </tr>
        </thead>
        <tbody>
          <?php if ( !empty($orders) ) : 
            foreach ($orders AS $i => $order) : ?>
          <tr>
            <!-- <?php print_r($order); ?> -->
            <td><?=$i + 1?></td>
            <td><?=$order['order_number']?></td>
            <td><?=$order['currency_sign'] . number_format($order['request_amount'], $order['currency_float'])?></td>
            <td><?=$order['currency_sign'] . number_format($order['order_amount'], $order['currency_float'])?></td>
            <td><?=$order['status_name']?>
            <td><a class='btn btn-sm btn-secondary' 
                  <?php if ( !empty($order['payment_id']) ) : ?>
                    href='/orders/detail/<?=$order['id']?>'>상세보기
                  <?php else : ?>
                    href='/orders/inventoryDetail/<?=$order['id']?>'>상세보기
                  <?php endif; ?>
                </a>
            </td>
          </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  <section>
    <h6>문의내역</h6>
    <ul>
      <li></li>
    </ul>
  </section>
  <!-- <section>
    <h6>제품정보</h6>
    <ul>
      <li>
        <a class='button' href='/product/regist'>상품등록</a>
      </li>
      <li>
        <a class='button' href='/product/exportData'>공급가 입력 csv down</a>
        <form action="<?//=site_url('product/attachProductSupplyPrice')?>" method="post" enctype="multipart/form-data">
          <p>
            <input type="file" name="file" class="form-control" id="file">
            <label>공급가 csv로 입력</label>
          </p>
          <input type="submit" name="submit" value="<?//=lang('Product.registration')?>" class="btn btn-dark" />
        </form>
      </li>
      <li>
        <form action="<?//=site_url('product/attachProductSpq')?>" method="post" enctype="multipart/form-data">
          <input type='hidden' name='type' value='spq_criteria'>
          <p>
            <input type="file" name="file" class="form-control" id="file">
            <label>SPQ csv로 입력</label>
          </p>
          <input type="submit" name="submit" value="<?//=lang('Product.registration')?>" class="btn btn-dark" />
        </form>
      </li>
      <li>
        <ul>
          <li>
            보류중인 제품 개수 : 
          </li>
          <li>
            등록된 제품 개수 : 
          </li>
          <li>
            주문가능한 제품 개수 :
          </li>
        </ul>
      </li>
    </ul>
  </section> -->
</main>