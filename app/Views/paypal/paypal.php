<main>
<form method='post' action='<?=base_url('paypal/paypal')?>'>
  <table class='w-75'>
    <thead>
      <tr>
        <th>NO</th>
        <th>담당자</th>
        <th>인보이스 번호</th>
        <th>인보이스 날짜</th>
        <th>buyer 이메일 또는 이름</th>
        <th>금액</th>
        <th>지불기일</th>
        <th>지불현황</th>
        <th>발행일</th>        
      </tr>
    </thead>
    <tbody>
      <?php if ( !empty($paypalList) ) : 
        foreach ( $paypalList AS $list ) : 
      ?>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <?php endforeach;
        endif; ?>        
    </tbody>
  </table>
</form>
</main>