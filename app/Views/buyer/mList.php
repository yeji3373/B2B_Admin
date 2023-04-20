<?php if (!empty($buyers)) : ?>
<div>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>업체(buyer)</th>
        <th>담당자</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($buyers as $i => $buyer) : ?>
      <tr>
        <td><?=$i + 1?></td>
        <td>
        <?php 
          echo $buyer['name'];
          if ( !empty($buyer['certificate_business']) ) :
          echo "<br/><a href='http://beautynetkorea.daouimg.com/b2b/documents/register/certification/".$buyer['certificate_business']."' target='blank'>사업자등록증 확인</a>";
          endif;
        ?>
        </td>
        <td>
          <?=empty($buyer['manager_name']) ? '미지정' : $buyer['manager_name']?>
        </td>
        <td>
          <a class='btn btn-outline-dark btn-sm' href='/buyer/detail/<?=$buyer['id']?>'>변경</a>
        </td>
      </tr>
    <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php else : ?>
<div>is empty</div>
<?php endif ?>