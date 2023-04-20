<main>
<title>업체(Buyer) 전체</title>
<?php if (!empty($buyers)) : ?>
<div class='buyer-list'>
  <table>
    <colgroup>
      <col style='width: 3%;'>
      <col style='width: 10%;'>
      <col style='width: 10%;'>
      <col style='width: 10%;'>
      <col style='width: 10%;'>
      <col style='width: 15%;'>
      <col style='width: 5%;'>
      <col style='width: 5%;'>
      <col style='width: 5%;'>
      <col style='width: 10%;'>
    </colgroup>
    <thead>
      <tr>
        <th rowspan='2'>No</th>
        <th colspan='5'>업체(buyer) 정보</th>
        <th rowspan='2'>담당자</th>
        <th rowspan='2'>margin구간</th>
        <th rowspan='2'>결제율</th>
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
        <td class='p-1'>
          <?php if ( !empty($buyer['certificate_business']) ) : ?>
            <img class='business_certificate' src='<?="http://beautynetkorea.daouimg.com/b2b/documents/register/certification/".$buyer['certificate_business']?>' alt='사업자등록증' style='width: 2rem;'>
          <?php else : ?>
            <span class='text-bg-danger'>사업자등록증 미등록</span>
          <?php endif; ?>
        </td>
        <td class='p-1'>
          <?=$buyer['business_number']?>
        </td>
        <td class='p-1'>
          <?=$buyer['phone']?>
        </td>
        <td class='p-1'>
          <?=$buyer['address']?>
        </td>
        <td class='p-1'>
          <div class='btn'>
            <?=$buyer['manager_id'] == 0 ? '미지정' : $buyer['manager_name']?>
          </div>
          <!-- <?php if ( !empty($managers) ) : ?>
          <select class='buyerManager' data-buyer='<?=$buyer['id']?>'>
            <option value <?=$buyer['manager_id'] != 0 ? 'selected' : ''?>>미지정</option>
            <?php foreach ($managers as $manager) : 
              echo "<option value='".$manager['idx']."'";
              if ( $manager['idx'] == $buyer['manager_id'] ) { echo 'selected'; }
              echo ">".$manager['name']."</option>";
            endforeach; ?>
          </select>
          <?php endif; ?> -->
        </td>
        <td class='p-1'>
          <?=$buyer['margin_level'] == 2 ? "B 구간" : "A 구간" ?>
        </td>
        <td class='p-1'>
          <?=$buyer['deposit_rate'] * 100 ?>%
        </td>
        <td class='p-1'>
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
</main>