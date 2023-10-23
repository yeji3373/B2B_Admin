let currentStepIndex = null;
$(document).ready(function() {
  $('.inventory-detail-container input[type=checkbox].order_excepted').each(function(index, item) {
    if ( $(item).closest($(item).data('cancelParent')).find($(item).data('cancelTarget')).length ) {
      $target = $(item).closest($(item).data('cancelParent')).find($(item).data('cancelTarget'));

      if ( $(item).val() == 1 ) { 
        $target.closest('tr').addClass('bg-danger bg-opacity-10');
      } else {
        $target.closest('tr').removeClass('bg-danger bg-opacity-10');
      }
    }
  });
  inventoryAmount();

  $('input[name="order[order_fix]"]').val($('.packaging-status option:selected').data('orderFix'));

  if ( $('.packaging-status').length ) {
    currentStepIndex = $('.packaging-status option').index($('.packaging-status option:selected'));
    if ( typeof $('.packaging-status option:selected').data('hasEmail') != 'undefined'
            && $('.packaging-status option:selected').data('hasEmail') == true ) {
      $(".email-send").removeClass('d-none');
    }
    if ( typeof $('.packaging-status option:selected').data('orderBy') != 'undefined') {
      if ( $('.packaging-status option:selected').data('orderBy') != '' ) {
        $(".packaging-order-by").val($('.packaging-status option:selected').data('orderBy'));
      }
    }

    if ( typeof $('.packaging-status option:selected').data('disabled') != 'undefined'
        && $('.packaging-status option:selected').data('disabled') == true ) {
      $(".status-save-btn").attr('disabled', true);
    }else{
      $(".status-save-btn").attr('disabled', false);
    }
  }
}).on('click', '.inventory-detail-container .btn', function(e) {
  if ( $(this).hasClass('email-send') ) {
    return false;
  }
  if ( $('.packaging_check').val() == true ) {
    let packagingCheck = confirm('재고 요청 완료 후에는 취소가 불가능합니다.\n계속 진행하겠습니까?');
    if ( !packagingCheck ) {
      return false;
    }
  }

  // Array.from($(".detail_items")).forEach((v, i) => {
  //   Array.from($(v).find('input')).forEach((value) => {
  //     if ( typeof $(value).data('compareTarget') != 'undefined' ) {
  //       if ( !$(value).prev($(value).data('compareTarget')).length ) {
  //         $(value).parent().prepend($("<input type='hidden'/>").attr('name', $(value).data('compareTarget')));
  //       }

  //       if ( $(value).data('compareValue') != $(value).val() ) {
  //         $(value).prev($('input[name="' + $(value).data('compareTarget') + '"]')).val(1);
  //       } else {
  //         $(value).prev($('input[name="' + $(value).data('compareTarget') + '"]')).val(0);
  //       }
  //     }

  //     // if ( typeof $(value).data('cancelTarget') != 'undefined' 
  //     //   && typeof $(value).data('cancelValue') != 'undefined') {
  //     //   if ( $(value).parent().find($(value).data('cancelTarget')).length ) {
  //     //     $(value).parent().find($(value).data('cancelTarget')).val($(value).data('cancelValue'));
  //     //   }
  //     // }
  //   });
  // });
  
  // $('.inventory-detail-container form').submit();
}).on('change', '.inventory-detail-container input[type=checkbox].order_excepted', function(){
  let temp = 0, $target = null;
  if ( typeof $(this).data('cancelTarget') != 'undefined'
      && typeof $(this).data('cancelValue') != 'undefined' 
      && typeof $(this).data('cancelParent') != 'undefined' ) {
    if ( $(this).closest($(this).data('cancelParent')).find($(this).data('cancelTarget')).length ) {
      $target = $(this).closest($(this).data('cancelParent')).find($(this).data('cancelTarget'));
      temp = $target.data('temp');

      if ( $(this).val() >= 1 ) { 
        $target.closest('tr').addClass('bg-danger bg-opacity-10');
        $target.val($(this).data('cancelValue'));
      } else {
        $target.closest('tr').removeClass('bg-danger bg-opacity-10');
        $target.closest('tr').find('input[type=number]').removeAttr('disabled');
        $target.val(temp);
      }
    }
  }
  inventoryAmount();
// }).on('change', '.packaging_check', function() {
//   if ( typeof $(this).data('target') != 'undefined' ) {
//     if ( $(this).parent().find($(this).data('target')).length ) {
//       Array.from($(this).parent().find($(this).data('target'))).forEach((v) => {
//         if ( $(this).val() == 1 ) {
//           $(v).attr('name', $(v).data('name'));
//         } else {
//           $(v).removeAttr('name');
//         }
//       });
//     }
//     inventoryAmount();
//   }
}).on('keyup change', '.request-amount-change', function(e) {
  $find = null, subtotal = 0, time = 1000, beforaValue = 0;

  if ( $(this).hasClass('prd-price') ) $find = $(this).closest('tr').find('.prd-qty');
  if ( $(this).hasClass('prd-qty') ) $find = $(this).closest('tr').find('.prd-price');

  if ( $(this).val().length >= 1 && $(this).closest('tr').find('.order_excepted').val() == 0) {
    if ( e.keyCode == 13 ) time = 0;
    setTimeout(() => {
      if ( $(this).val() == '' || $(this).val() == 0 ) {
        if ( $(this).parent('p').length ) {
          beforaValue = $.trim($(this).parent('p').prev('p').text().replace(/\, | \$/gi, ''));
        }
        console.log("beforaValue ", beforaValue);
        $(this).val(beforaValue);
      } 

      subtotal = parseFloat($(this).val()) * parseFloat($find.val());
      $(this).closest('tr').find('.request-subtotal').val(subtotal.toFixed(2));
      inventoryAmount();
    }, time);
  }
}).on('change', '.packaging-status', function() {
  if ( $('.package').length ) {
    if ( typeof $('.packaging-status option:selected').data('disabled') != 'undefined'
        && $('.packaging-status option:selected').data('disabled') == true ) {
      $(".status-save-btn").attr('disabled', true);
    }else{
      $(".status-save-btn").attr('disabled', false);
    }
    
    if ( currentStepIndex >= $('.packaging-status option').index($('.packaging-status option:selected')) )  {
      $('.package').removeAttr('name');
    } else {
      Array.from($('.package')).forEach((v) => {
        if ( typeof $(v).data('name') != 'undefined' ) {
          $(v).attr('name', $(v).data('name'));
        }
      });
    }
    //결제요청을 하면 그게 곧 order_fixed란 뜻이라 packaging_status의 payment_request 컬럼값을 가져와서 orderFix로 넣었음.
    if ( typeof $('.packaging-status option:selected').data('orderFix') != 'undefined') {
      $('input[name="order[order_fix]"]').val($('.packaging-status option:selected').data('orderFix'));
      $('.packaging-status option:selected').data('orderFix')
    }
  }

  if ( typeof $(this).find('option:selected').data('orderBy') != 'undefined') {
    if ( $(this).find('option:selected').data('orderBy') != '' ) {
      $(".packaging-order-by").val($(this).find('option:selected').data('orderBy'));
    }
  }

  if ( typeof $(this).find('option:selected').data('hasEmail') != 'undefined') {
    if ($(this).find('option:selected').data('hasEmail') == true ) {
      $(".email-send").removeClass('d-none');
    } else {
      $(".email-send").addClass('d-none');
    }
  }
}).on('change', '.require-option-use', function() {
  if (typeof $(this).data('addTarget') == 'undefined') return;
  
  let target = $(this).data('addTarget');
  let optionIds = $(this).closest('.requirement-item').find(target);
  console.log(optionIds.attr('name'));
  if (optionIds.val() != '' ) {
    optionIdsVal = optionIds.val().split(',');
    if ( $.inArray(String($(this).val()), optionIdsVal) >= 0) {
      _removeId = $.inArray(String($(this).val()), optionIdsVal);
      optionIdsVal.splice(_removeId);
    } else {
      optionIdsVal.push($(this).val());
    }
    optionIds.val(optionIdsVal.join(','));
  } else {
    optionIds.val($(this).val());
  }
  // console.log(optionIds.val());
  return;
});

function inventoryAmount() {
  let target = 'inventory_fixed_amount';
  if ( $("#pay-step").length ) {    
    if ( $("#pay-step").val() == 2 ) {
      target = 'fixed_amount'
    } else if ( $("#pay-step").val() == 3 ) {
      target = 'decide_amount';
    }

    if ( $(".request-subtotal").length ) {
      let inventory_amount = 0;
      Array.from($(".request-subtotal")).forEach((v) => {
        if ($(v).attr('type') == 'text') {
          inventory_amount += parseFloat($(v).val());
        }
      });
      
      if ( $('input[name="order[' + target + ']"]').length ) {
        inventory_amount = inventory_amount.toFixed(2);
        $('input[name="order[' + target + ']"]').val(inventory_amount);
        if ( $("." + target ).length ) {
          $("." + target).text(inventory_amount);
        }
      }
    }
  }
}
