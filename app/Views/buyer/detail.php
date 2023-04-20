<main>
<h3>업체정보</h3>
<?php if ( session()->has('error') ) : ?>
  <div class="notification error">
    <?= session('error') ?>
  </div>
<?php endif ?>
<?php if ( !empty($buyer) ) : ?>
<form method='post' action='<?=site_url('buyer/edit')?>' accept-charset='utf-8'>
  <?=form_hidden('buyer[id]', $buyer['id'])?>
  <div>
    <div>
      <label>업체(바이어)명</label>
      <div><?=$buyer['name']?></div>
    </div>
    <div>
      <label>담당자</label>
      <div>
        <?php 
        if ( !empty($managers) ) : 
          echo "<select class='buyerManager' name='buyer[manager_id]' data-buyer='".$buyer['id']."'>";
          echo "<option value ".($buyer['manager_id'] != 0 ? 'selected' : '').">미지정</option>";
          foreach ( $managers as $manager ) : 
            echo "<option value='".$manager['idx']."'";
            if ( $manager['idx'] == $buyer['manager_id'] ) { echo 'selected'; }
            echo ">".$manager['name']."</option>";
          endforeach;
          echo "</select>";
        endif;
        ?>
      </div>
    </div>
    <div>
      <label>사업자등록번호/<Br/>사업자등록증</label>
      <div class='d-flex flex-column'>
        <?=$buyer['business_number']?>
        <?php if ( empty($buyer['certificate_business']) ) : ?>
          <span class='text-bg-danger'>사업자등록증 미등록</span>
        <?php else : ?>
          <img class='business_certificate' src='<?="http://beautynetkorea.daouimg.com/b2b/documents/register/certification/".$buyer['certificate_business']?>' alt='사업자등록증' style='width: 10rem;'>
        <?php endif ?>
      </div>
    </div>
    <div class='sell-region-group'>
      <label>판매국가</label>
      <div class='d-flex flex-column mt-2'>
      <?php if ( !empty($regions) ) : ?>
        <div class='d-flex flex-column border border-dark position-relative region-group'>
          <label class='fw-bold position-absolute'>Region</label>
          <div class='d-flex flex-row flex-wrap p-2'>
            <?php foreach($regions as $region) : ?>
            <div>
              <?=$region['region'].'('.$region['region_en'].')'?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if ( !empty($countries) ) : ?>
        <div class='d-flex flex-column border border-dark position-relative mt-3 country-group'>
          <label class='fw-bold position-absolute'>Country</label>
          <div class='d-flex flex-row flex-wrap p-2'>
            <?php foreach($countries as $country) : ?>
            <div><?=$country['name'].'('.$country['name_en'].')'?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      </div>
    </div>
    <div>
      <label>주소</label>
      <div><?=$buyer['address']?></div>
    </div>
    <div>
      <label>연락처</label>
      <div><?=$buyer['phone']?></div>
    </div>
    <div>
      <label>Margin</label>
      <div>
        <?php 
        if ( !empty($margin) ) : 
          echo "<select name='buyer[margin_level]'>";
          foreach($margin as $m) : 
          echo "<option value='{$m['margin_level']}'";
          if ($m['margin_level'] == $buyer['margin_level']) {
            echo "selected";
          }
          echo ">";
            echo "{$m['margin_section']} 구간";
          echo "</option>";
          endforeach;
          echo "</select>";
        endif;
        ?>
      </div>
    </div>
    <div>
      <label>과세 허용</label>
      <div>
        <select name='buyer[tax_check]'>
          <option value=0 <?=$buyer['tax_check'] == 0 ? 'selected' : ''?>>허용 안함</option>
          <option value=1 <?=$buyer['tax_check'] == 1 ? 'selected' : ''?>>허용</option>
        </select>
      </div>
    </div>
    <div class='currency-group'>
      <label>우대 환율</label>
      <!-- <$buyer['currency_rate_idx']> -->
      <div class='d-flex flex-column'>
        <div class='border border-dark rounded pt-2 px-2 pb-1 mb-2 mt-3 position-relative'>
          <label class='position-absolute fw-bold bg-whight start-1'>적용 중인 기준 환율</label>
          <div class='d-flex flex-row'>
            <?php
            foreach ($currency as $c) :
              if ( $c['default_currency'] == 1 ) {
                $exchange_check_rate = $c['exchange_rate'];
              }
              echo "<div class='d-flex flex-row p-1'>";
              echo "<div class='me-1'>{$c['currency_code']}</div>";
              echo "<div style='width: 80%;'>".number_format($c['exchange_rate'])."</div>";
              echo "</div>";
            endforeach;
            // echo "<input type='hidden' class='exchange_check_rate' value='{$exchange_check_rate}'>";
            ?>
          </div>
        </div>
        <div>
          <?php 
          if ( !empty($currency) ) :
            echo "<select name='currencyRate[currency_idx]'>";
            echo "<option value>선택</option>";
            foreach ($currency as $c) :
              echo "<option value='{$c['idx']}'";
              echo " data-exchange='{$c['exchange_rate']}'>{$c['currency_code']}</option>";
            endforeach;
            echo "</select>";
          endif;
          ?>
          <input type='hidden' name='currencyRate[default_set]' value='0'>
          <input type='text' maxlength='7' name='currencyRate[exchange_rate]' value=''>
        </div>
      </div>
    </div>
    <div>
      <label>선입금(결제) 비율</label>
      <div>
        <input type='text' name='buyer[deposit_rate]' value='<?=$buyer['deposit_rate'] * 100?>' style='width: 3rem; text-align: right;'>&nbsp;%
      </div>
    </div>
    <!-- <div>
      <label>선입금된 금액</label>
      <div><input type='text' name='deposit_rate' value>%</div>
    </div> -->
    <div>
      <label>승인</label>
      <div>
        <select name='buyer[confirmation]'>
          <option value='0' <?php echo $buyer['confirmation'] == 0 ? 'selected' : ''?>>미승인</option>
          <option value='1' <?php echo $buyer['confirmation'] == 1 ? 'selected' : ''?>>승인</option>
        </select>
      </div>
    </div>
    <?php if ( !empty($users) ) : ?>
    <div>
      <label>로그인 정보</label>
      <div class='d-flex flex-column'>
        <?php foreach($users as $i =>  $user) : ?>
        <div class='d-flex flex-row border border-dark p-0 buyer-memeber-list'>
          <div class='buyer-member d-flex flex-column border-end border-dark'>
            <?=form_hidden("user[{$i}][idx]", $user['idx'])?>
            <label class='w-100 border-bottom border-dark p-1'>ID</label>
            <div class='p-1 text-center'>
              <?=$user['id']?>
            </div>
          </div>
          <div class='buyer-member d-flex flex-column border-end border-dark'>
            <label class='w-100 border-bottom border-dark p-1'>Name</label>
            <div class='p-1 text-center'>
              <?=$user['name']?>
            </div>
          </div>
          <div class='buyer-member d-flex flex-column border-end border-dark w-100'>
            <label class='w-100 border-bottom border-dark p-1'>email</label>
            <div class='p-1 text-center'>
              <?=$user['email']?>
            </div>
          </div>
          <div class='buyer-member d-flex flex-column border-end border-dark'>
            <label class='w-100 border-bottom border-dark p-1'>active</label>
            <div class='p-1'>
              <select name='user[<?=$i?>][active]'>
                <option value>선택</option>
                <option value='0' <?=$user['active'] == 0 ? 'selected' : ''?>>비활성화</option>
                <option value='1' <?=$user['active'] == 1 ? 'selected' : ''?>>활성화</option>
              </select>
            </div>
          </div>
          <div class='buyer-member d-flex flex-column'>
            <label class='w-100 border-bottom border-dark p-1'>등록일</label>
            <div class='p-1 text-center'>
              <span><?=date('Y-m-d', strtotime($user['created_at']))?></span>
            </div>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>
    <?php endif ?>
    <div>
      <label>등록일</label>
      <div><?=$buyer['created_at']?></div>
    </div>
  </div>
  <button class='btn mt-3 text-bg-danger px-5 py-2'>적용</button>
</form>
<?php endif ?>
</main>