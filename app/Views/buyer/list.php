<main>
  <form method='GET' action='<?=base_url('buyer/list')?>'>
  <input type='hidden' name='page' value='1'>
  <fieldset class='my-2 px-2 pb-2 border border-dark border-secondaty d-flex flex-row justify-content-between align-items-end'>
    <div class='d-flex flex-row'>
      <div class='form-group me-2'>
        <label>업체명</label>
        <input class='form-control form-control-sm w-100'
            type='text' 
            name='buyer_name' 
            value='<?=isset($_GET['buyer_name']) ? $_GET['buyer_name'] : ''?>'>
      </div>
      <div class='form-group me-2'>
        <label>가입일</label>
        <div>
          <input class='form-control-sm' style='border: 1px solid #ced4da;' type='text' name='start_date' value='<?=!empty($_GET['start_date']) ? $_GET['start_date'] : ''?>'>
           ~ <input class='form-control-sm' style='border: 1px solid #ced4da;' type='text' name='end_date' value='<?=!empty($_GET['end_date']) ? $_GET['end_date'] : ''?>'>
          <label>
          <input type='checkbox' name='dateYn' class='form-check-input value-change' value='1' 
          <?= $dateYn == 1 ? ' checked' : '' ?> />
            전체기간
          </label>
        </div>
      </div>
      <div class='form-group me-2'>
        <label>담당자</label>
        <select class='form-select form-select-sm' name='managers'>
          <option value=''>전체</option>
            <?php if ( !empty($managers) ) : 
              foreach ( $managers AS $m ) : ?>
              <option value='<?=$m['idx']?>' <?=(isset($_GET['managers']) && $_GET['managers'] == $m['idx']) ? 'selected' : ''?> ><?=$m['name']?></option>
            <?php endforeach;
            endif; ?>
        </select>
      </div>
      <div class='form-group me-2'>
        <label>Region</label>
        <select class='form-select form-select-sm' name='regions'>
          <option value=''>전체</option>
          <?php if ( !empty($regions) ) : 
            foreach ( $regions AS $r ) : ?>
            <option value='<?=$r['id']?>' <?=(isset($_GET['regions']) && $_GET['regions'] == $r['id']) ? 'selected' : ''?> ><?=$r['region']?></option>
          <?php endforeach;
          endif; ?>
        </select>
      </div>
      <div class='form-group me-2'>
        <label>margin구간</label>
        <select class='form-select form-select-sm' name='margin'>
          <option value=''>전체</option>
          <?php if ( !empty($margin) ) : 
            foreach ( $margin AS $m ) : ?>
            <option value='<?=$m['idx']?>' <?=(isset($_GET['margin']) && $_GET['margin'] == $m['idx']) ? 'selected' : ''?> ><?=$m['margin_section']?> 구간</option>
          <?php endforeach;
          endif; ?>
        </select>
      </div>
      <div class='form-group'>
        <label>승인</label>
        <select class='form-select form-select-sm' name='confirmation'>
          <option value='2'>전체</option>
          <option value='0' <?= (isset($_GET['confirmation']) && $_GET['confirmation'] == '0') ? 'selected' : ''?>>미승인</option>
          <option value='1' <?= (isset($_GET['confirmation']) && $_GET['confirmation'] == '1') ? 'selected' : ''?>>승인</option>
        </select>
      </div>
    </div>
    <input type='submit' class='btn btn-primary search-btn' value='검색'>
  </fieldset>
  </form>
  <title>업체(Buyer) 전체</title>
  <?php if (!empty($buyers)) : ?>
  <div class='buyer-list'>
    <table>
      <colgroup class='print-hide'>
        <col style='width: 3%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 10%;'>
        <col style='width: 15%;'>
        <col style='width: 5%;'>
        <col style='width: 5%;'>
        <col style='width: 5%;'>
        <col style='width: 5%;'>
        <col style='width: 3%;'>
        <col style='width: 10%;'>
      </colgroup>
      <thead class='print-hide'>
        <tr>
          <th rowspan='2'>No</th>
          <th colspan='5'>업체(buyer) 정보</th>
          <th rowspan='2'>담당자</th>
          <th rowspan='2'>margin구간</th>
          <th rowspan='2'>결제율</th>
          <th rowspan='2'>가입일</th>
          <th rowspan='2'>승인</th>
          <th rowspan='2'>view</th>
        </tr>        
        <tr>
          <th>업체명</th>
          <th>사업자등록증</th>
          <th>사업자등록번호</th>
          <th>연락처</th>
          <th class='border-dark border-end'>주소</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($buyers as $i => $buyer) : ?>
        <tr>
          <td class='p-1'>
            <?=$i + 1?>
          </td>
          <td class='p-1'>
            <?=$buyer['name']?>
          </td>
          <td class='p-1 print-hide'>
            <?php if ( !empty($buyer['certificate_business']) ) :           
              $ext = substr(strchr($buyer['certificate_business'], '.'), 1);
              $path = "//beautynetkorea.daouimg.com/b2b/documents/register/certification/"; ?>
              <span class='business_certificate_show' data-src='<?=$path.$buyer['certificate_business']?>' data-ext='<?=$ext?>'>사업자등록증 확인</span>
            <?php else : ?>
              <span class='text-bg-danger'>사업자등록증 미등록</span>
            <?php endif; ?>
          </td>
          <td class='p-1 print-hide'>
            <?=$buyer['business_number']?>
          </td>
          <td class='p-1 print-hide'>
            <?=$buyer['phone']?>
          </td>
          <td class='p-1'>
            <?=$buyer['address']?>
          </td>
          <td class='p-1'>
            <div class='btn'>
              <?=$buyer['manager_id'] == 0 ? '미지정' : $buyer['manager_name']?>
            </div>
          </td>
          <td class='p-1'>
            <?=$buyer['margin_level'] == 2 ? "B 구간" : "A 구간" ?>
          </td>
          <td class='p-1'>
            <?=$buyer['deposit_rate'] * 100 ?>%
          </td>
          <td>
            <?=date('Y-m-d', strtotime($buyer['created_at']))?>
          </td>
          <td class='print-hide'>
            <?=$buyer['confirmation'] == 0 ? '미승인' : '승인'?>
          </td>
          <td class='p-1 print-hide'>
            <a class='btn-link' href='/buyer/detail/<?=$buyer['id']?>'>변경</a>
          </td>
        </tr>
      <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <?php else : ?>
  <div>is empty</div>
  <?php endif ?>
  <?=$pager->links('default', 'pager')?>
</main>