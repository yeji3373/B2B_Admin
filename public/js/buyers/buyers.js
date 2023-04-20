$(document).on('change', '[name=currency_id]', function() {
  console.log($(this).find('option:selected').data('exchange'));
  // $("input[name=exchange_rate]").val($(this).find('option:selected').data('exchange'));
  $("input[exchange_check_rate]").val($(this).find('option:selected').data('exchange'));
}).on('keyup', '[name=exchange_rate]', function(e) {
  let reg = /^[0-9]+$/g;
  let compare = false;

  if ( e.keyCode == 13 || $(this).val().length > 1 ) {
    if ( $("select[name=currency_id] option:selected").val() == '' ) {
      $(this).val("");
      alert('환율을 적용할 범주를 먼저 선택해주세요.');
      return;
    }

    if ( $(this).val().length > 2 ) {
      if ( !reg.test($(this).val())) {
        $(this).val("");
        alert('숫자만 입력 가능');
        return;
      } else {
        // if ( $("input[class=exchange_check_rate]").val() < $(this).val() ) {
        //   if ( compare === false ) {
        //     if (!confirm("기준 금액보다 큽니다. 그냥 진행?")) {
        //       compare = true;
        //       $(this).val();
        //     }
        //   }
        // }
      }
    }  
  }
}).on('click', 'img.business_certificate', function() {
  let src = $(this).attr('src');
  let main = $(this).closest('main');

  if ( main.length > 0 ) {
    main.addClass('position-relative');
    if ( $(".certificate_viewer").length == 0 ) {
      main.append("<div class='certificate_viewer'>\
                    <div class='position-relative'>\
                      <button class='bg-white btn btn-close'></button>\
                      <img src='" + src +"' style='width: 100%; height: 100%;'>\
                    </div> \
                  </div>");
    } else {
      $(".certificate_viewer").removeClass('d-none');
      $(".certificate_viewer img").attr('src', src);
    }
  }
}).on('click', '.certificate_viewer .btn-close', function() {
  console.log("btn click");
  $(".certificate_viewer").addClass('d-none');
}).on('submit', 'form', function(e) {
  // e.preventDefault();

  if ( $('[name="buyer[manager_id]"]').val() == '' ) {
    alert("담당자 지정 필요."); return false;
  }

  if ( $('[name="buyer[deposit_rate]"]').val() > 100 ) {
    alert("100%를 넘길 수 없음"); 
    $('[name="buyer[deposit_rate]"]').val(50);
    return false;
  } else if ( $('[name="buyer[deposit_rate]"]').val() <= 0 ) {
    alert("최소 10%여야 함");
    $('[name="buyer[deposit_rate]"]').val(50);
    return false;
  }

  if ( $('[name="buyer[confirmation]"]').val() == 0 ) {
    if ( !confirm('승인 안하고 진행?') ) return false;
  }

  if ( $('[name="buyer[confirmation]"').val() == 1 ) {
    let activeCnt = 0;
    $.each($(".buyer-memeber-list select"), function(idx, val) {
      console.log("aaaaa ", $(this).children('option').eq(val.selectedIndex).val());
      if ($(this).children('option').eq(val.selectedIndex).val() == 1 ) {
        activeCnt++;
        return false;
      }
    });

    if ( activeCnt <= 0 ) {
      alert('최소 한개의 계정 활성화 해야함. 하지 않을 경우, 로그인 불가');
      return false;
    }
  }
  
  let exchangeRateBasis = $('[name="currencyRate[currency_idx]"] option:selected').data('exchange');
  if ( $('[name="currencyRate[exchange_rate]"]').val() == exchangeRateBasis ) {
    alert("기준 환율과 동일함.");
    $('[name="currencyRate[currency_idx]"] option').eq(0).prop("selected", true);
    return false;
  }

  if ($('[name="currencyRate[exchange_rate]"]').val() == '') {
    $('[name="currencyRate[currency_idx]"] option').eq(0).prop("selected", true);
  }
});