let optional_config;
$(document).ready(function() {
  optional_config = { 
    dateFormat: 'Y-m-d',
    enableTime: false,
    defaultDate: new Date(),
    onReady: function() {
      console.log("달력이 준비상태가 되었을 때");
      // let flatpickrInstance = this;
      // console.log(flatpickrInstance);
    },
    onClose: function() {
      // console.log("달력이 닫힐 때");
    },
    onOpen: function() {
      // console.log("달력이 열릴 때");
    }
  }
  
  if ( $('input[name=start_date]').length && $('input[name=end_date]') ) {
    flatpickr('input[name=start_date]', $.merge({'defaultDate': new Date().setDate(new Date().getDate() - 7)}, optional_config));
    flatpickr('input[name=end_date]', optional_config);
  }

  if ( $('.delivery_apply_check').length ) {
    console.log("a");
    if ( typeof $('.delivery_apply_check').data('disabledTarget') != 'undefined' ) {
      disabled = $('.delivery_apply_check').data('disabledTarget');
      if ( $('.delivery_apply_check').is(':checked') ) {
        $(disabled).removeAttr('disabled');
      } else {
        $(disabled).attr('disabled', true);
      }
    }
  }
}).on('click', '.btn-pi', function() {
  if ( $(this).data('type') == '' || $(this).data('type').length == 0) return;

  $form = $(this).closest('form.pi-form');
  $type = $(this).data('type');

  if ( $(this).hasClass('display-except') ) $display = 0;
  else $display = 1;

  $form.children('[name=piControllType]').val($type);

  if ( $type == 'edit' ) {
    if ( !$form[0].reportValidity() ) return;
    
    // if ( $("[name='order[order_amount]']").length && $("[name='order[order_amount]']").val() > 0 ) {
    //   if ( !$form.find('[name=order_amount]').length ) {
    //     $form.append($("<input/>")
    //                       .attr({'type' : 'hidden', 'name': 'order_amount'})
    //                       .val($.trim($("[name='order[order_amount]']").val())));
    //   } else {
    //     $form.children('[name=order_amount]').val($.trim($("[name='order[order_amount]']").val()));
    //   }
    // } else {
    //   alert('수정 불가');
    //   return;
    // }

    $form.submit();
  }

  if ( $type == 'receipt' ) {
    if ( $form.children('[name=payment_status]').val() == 100 ) {
      $form.submit();
    } else {
      if ( $form.children('[name=payment_status]').val() == 0 ) {
        alert('결제 전인 경우에는 2차 PI 발행이 되지 않음');
        return;
      }
    }
  }

  if ( $type == 'cancel') {
    if ( confirm('취소하시겠습니까?') ) {
      $form.submit();
    }
  }

  if ( $type == 'refund') {
    console.log($form.serializeArray());
    // return false;
    if ( confirm('환불하시겠습니까?') ) {
      $form.submit();
    }
  }
  return false;
}).on('change', '.delivery_apply_check', function() {
  if ( typeof $(this).data('disabledTarget') != 'undefined' ) {
    disabled = $(this).data('disabledTarget');

    if ( $(this).is(':checked') ) {
      $(this).closest('form').find(disabled).removeAttr('disabled');
    } else {
      $(this).closest('form').find(disabled).attr('disabled', true);
    }
  }
}).on('click', '.invoice-edit', function(e) {
  if ( e.target.classList.contains('invoice-edit') ) {
    // $('body').css('overflow-y', '');
    $('body').removeClass('overflow-hidden');
    $('.invoice-edit').addClass('d-none');
  }
}).on('change', '.pi-edit-container [name="receipt[rq_percent]"]', function() {
  // let amount = parseFloat($('[name=order_amount]').val());
  let amount = parseFloat($('[name="order[order_amount]"]').val());
  let paid = parseFloat($(this).closest('form').find('[name=amount_paid]').val());
  let rqAmount = 0, dueAmount = 0;
  let per = $(this).val();

  if ( typeof $('[name="order[order_amount]"]') == 'undefined' || amount <= 0 ) return;
  if ( per != '-') {
    rqAmount = ((amount - paid) * per).toFixed(2);
    dueAmount = ((amount - paid) - rqAmount).toFixed(2);
    
    console.log('change amount : ', amount, ' paid : ', paid, ' rqAmount : ', rqAmount, ' dueAmount : ', dueAmount,  ' per : ', per);
    
    $(this).closest('form').find('[name="receipt[rq_percent]"]').val(per);
    $(this).closest('form').find('.receipt-rq-amount').text(rqAmount);
    $(this).closest('form').find('[name="receipt[rq_amount]"]').val(rqAmount);
    $(this).closest('form').find('.receipt-due-amount').text(dueAmount);
    $(this).closest('form').find('[name="receipt[due_amount]"]').val(dueAmount);

    if ( $(".receipt_num") == 1 ) {
      if ( parseFloat(per) == parseFloat(1) ) {
        console.log('1');
        if ( $(".shipment_info").hasClass('d-none') )  {
          $(".shipment_info").removeClass('d-none');
        }
      } else {
        if ( !$(".shipment_info").hasClass('d-none') )  {
          $(".shipment_info").addClass('d-none');
        }
      }
    }
  } else {
    $(this).closest('form').find('[name="receipt[rq_percent]"]').val(0);
    $(this).closest('form').find('.receipt-rq-amount').text(0);
    $(this).closest('form').find('[name="receipt[rq_amount]"]').val(0);
    $(this).closest('form').find('.receipt-due-amount').text(0);
    $(this).closest('form').find('[name="receipt[due_amount]"]').val(0);
  }
}).on('click', '.pi-edit-container .receipt-rq-amount', function() {
  let classes = 'color-transparent btn-outline-secondary btn-close';
  let pClasses = 'd-flex flex-row align-items-center justify-content-center';
  let tempP1 = parseFloat($(this).text().trim());
  let tempP2 = parseFloat($('[name="order[order_amount]"]').val() - $('[name=amount_paid]').val() - parseFloat(tempP1)).toFixed(2);
  
  console.log("temp 1 ", tempP1, ' temp 2 ', tempP2);
  if ( $(this).hasClass(classes) ) {
    $(this).removeClass(classes);
    $(this).parent().removeClass(pClasses);
    $("[name='receipt[rq_percent]']").attr('disabled', false);
    $("[name='receipt[due_amount]'").attr('disabled', true).val(tempP2);
    $('.receipt-due-amount').text(tempP2)
    $("[name='receipt[rq_amount]']").attr('type', 'hidden').val(tempP1);    
  } else {
    $(this).addClass(classes);
    $(this).parent().addClass(pClasses);
    $("[name='receipt[rq_percent]']").attr('disabled', true);
    $("[name='receipt[due_amount]'").attr('disabled', false);
    $("[name='receipt[rq_amount]']").attr('type', 'text').addClass('mx-1').focus();
  }
}).on('keyup', '.pi-edit-container [name="receipt[rq_amount]"]', function(e) {
  let remainPrice = ($('[name="order[order_amount]"]').val() - $('[name=amount_paid]').val());
  if ( e.keyCode == 27 ) {
    $('.pi-edit-container .receipt-rq-amount').click();
    return;
  }
  
  if ( $(this).val().length > 2 ) {
    $this = $(this);
    setTimeout(function() {
      remainPrice = ($('[name="order[order_amount]"]').val() - $('[name=amount_paid]').val());

      if ( parseFloat($this.val()) <= 0 ) {
        alert('입력값은 0보다 커야합니다');
        return;
      }

      // if ( remainPrice < $this.val() ) {
      //   alert("남은 금액보다 클 수 없습니다");
      //   return;
      // }
      let due_amount = parseFloat(remainPrice - $this.val()).toFixed(2);
      console.log(remainPrice);
      $("[name='receipt[due_amount]']").val(due_amount);
      $('.receipt-due-amount').text(due_amount);
    }, 1000);
  }

  if ( parseFloat($(this).val()) > parseFloat(remainPrice) ) {
    alert("결제 총 금액보다 클 수 없습니다.");
    $(this).val(parseFloat(remainPrice).toFixed(2));
    return;
  }
}).on('change', '.pi-edit-container .detail-order-excepted', function() {
  if ($(this).is(":checked") == true ) {
    $(this).siblings('input[type=hidden]').val(1);
  } else {
    $(this).siblings('input[type=hidden]').val(0);
  }

  // if ( $(".detail-order-excepted:checked").length > 0 ) {
    if ( $(".detail-order-excepted:checked").length == $(".detail-order-excepted").length ) {
      console.log("취소는 어떨런지?");
      // return;
    }
  //   $('[name=order_check]').val(1);
  // } else $('[name=order_check]').val(0);
// }).on('change', '.pi-edit-container [name="delivery[shipment_id]"]', function() {
//   if ( $(this).val() != '' ) {
//     if ( $("[name='delivery[forword]']").prop('disabled') == true ) {
//       $("[name='delivery[forword]']").prop('disabled', false);
//     }
//   }
}).on('change', '.pi-edit-container input[type=checkbox]', function() {
  if ( $(this).hasClass('check_except') ) {
    if ( $(this).data('value') ) {
      if ( $(this).is(":checked") === true )  {
        $(this).val($(this).data('value'));
      } else $(this).val(0);
    }
    return;
  }
  $(this).val(Number($(this).is(':checked')));
// }).on('change', '.pi-edit-container [name="receipt[display]"]', function() {
//   $(this).val(Number($(this).is(':checked')));
}).on('change', '.pi-edit-container [name="receipt[payment_status]"]', function() {
  if ( $(this).val() == 100 ) {
    $("#payment_date").prop("disabled", false).prop('readonly', false).prop("required", true);
  // } else if ( $(this).val() == -200 ) {
  //   $("#payment_date").attr("name", "receipt[refund_date]").prop("disabled", false);
  } else {
    $("#payment_date").removeAttr("name").prop("disabled", true).prop('required', false);
  }
}).on('focusin', '.pi-edit-container [name="receipt[payment_date]"]', function() {
  $(this).prop('readonly', true);
}).on('focusout', '.pi-edit-container [name="receipt[payment_date]"]', function() {
  if ( $(this).val() == '' ) {
    $(this).prop('readonly', false);
  }
}).on('change', '.pi-edit-container [name="receipt[delivery_id]"]', function() {
  $parent = $(this).closest('tbody tr');
  if ( $(this).is(":checked") === true ) {
    $parent.find('.required').prop('required', true);
  } else {
    $parent.find('.required').prop('required', false);
  }
}).on('keypress', '.pi-edit-container form', function(e) {
  if ( e.keyCode == 13 ) {
    e.preventDefault();
    return false;
  }
});


// paypal 임시
$(document).ready(function() {
  flatpickr("input[name='invoice_date']", optional_config);
  flatpickr("input[name='due_date']", optional_config);
  // flatpickr(".expiration_date", $.merge({'defaultDate': ''}, optional_config));
}).on('change', 'select[name=term_type]', function() {
  let target = $(this).find('option:selected').attr('aria-target');
  if ( $(this).find('option:selected').attr('aria-hidden') == 'false' ) {
    $(target).removeClass('d-none');
  } else {
    $(target).addClass('d-none');
  }

  if ( $(this).find("option:selected").attr('aria-disabled') == 'false' )  {
    $(target).attr('disabled', false);
  } else $(target).attr('disabled', true);
// }).on('keyup', '.item-group input[type=text]', function(e) {
//   console.log("target ", e.target);
//   console.log('value ', $(e.target).val());
}).on('click', 'div.add_items', function() {
  let target = $("div.item-group").first();
  let clone = $(target).clone();
  let count = typeof $(target).attr('aria-count') == 'undefined' ? 0 : parseInt($(target).attr('aria-count'));
  let btn = $("<div/>").text('닫기')
                  .addClass('position-absolute top-50 translate-middle end-0 text-decoration-underline fw-bold')
                  .attr('role', 'button')
                  .click(function() {
                    $(this).parent().empty().detach();
                    // $('.item-container div.item-group').remove(a);
                  });  
  count++;
  
  $(target).attr('aria-count', count);
  
  $.each(clone.find('input[type=text]'), function(i, v) {
    $(v).val($(v).attr('aria-init'));
    if ( typeof $(v).attr('aria-name') !== 'undefined' ) {
      $(v).attr('name', 'items[' + count + '][' + $(v).attr('aria-name') + ']');
    }
  });  
  clone.addClass('mt-3');
  clone.append(btn);

  $('.item-container').append(clone);
}).on('click', '.invoiceDetail', function() {
  getData('')
});