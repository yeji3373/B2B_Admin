let currentStepIndex = null;
$(document).ready(function() {
  inventoryAmount();

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

  Array.from($(".detail_items")).forEach((v, i) => {
    Array.from($(v).find('input')).forEach((value) => {
      if ( typeof $(value).data('compareTarget') != 'undefined' ) {
        if ( !$(value).prev($(value).data('compareTarget')).length ) {
          $(value).parent().prepend($("<input type='hidden'/>").attr('name', $(value).data('compareTarget')));
        }

        if ( $(value).data('compareValue') != $(value).val() ) {
          $(value).prev($('input[name="' + $(value).data('compareTarget') + '"]')).val(1);
        } else {
          $(value).prev($('input[name="' + $(value).data('compareTarget') + '"]')).val(0);
        }
      }

      // if ( typeof $(value).data('cancelTarget') != 'undefined' 
      //   && typeof $(value).data('cancelValue') != 'undefined') {
      //   if ( $(value).parent().find($(value).data('cancelTarget')).length ) {
      //     $(value).parent().find($(value).data('cancelTarget')).val($(value).data('cancelValue'));
      //   }
      // }
    });
  });
  
  // $('.inventory-detail-container form').submit();
}).on('change', '.inventory-detail-container input[type=checkbox].order_excepted', function(){
  let temp = 0, $target = null;
  if ( typeof $(this).data('cancelTarget') != 'undefined'
      && typeof $(this).data('cancelValue') != 'undefined' 
      && typeof $(this).data('cancelParent') != 'undefined' ) {
    if ( $(this).closest($(this).data('cancelParent')).find($(this).data('cancelTarget')).length ) {
      $target = $(this).closest($(this).data('cancelParent')).find($(this).data('cancelTarget'));
      temp = $target.data('temp');

      if ( $(this).val() == 1 ) { 
        $target.closest('tr').addClass('bg-danger bg-opacity-10');
        $target.val($(this).data('cancelValue'));
      } else {
        $target.closest('tr').removeClass('bg-danger bg-opacity-10');
        $target.val(temp);
      }
    }
  }
  inventoryAmount();
}).on('change', '.packaging_check', function() {
  if ( typeof $(this).data('target') != 'undefined' ) {
    if ( $(this).parent().find($(this).data('target')).length ) {
      Array.from($(this).parent().find($(this).data('target'))).forEach((v) => {
        if ( $(this).val() == 1 ) {
          $(v).attr('name', $(v).data('name'));
        } else {
          $(v).removeAttr('name');
        }
      });
    }
    inventoryAmount();
  }
}).on('keyup', '.request-amount-change', function(e) {
  $find = null, subtotal = 0, time = 1000;

  if ( $(this).hasClass('prd-price') ) $find = $(this).closest('tr').find('.prd-qty');
  if ( $(this).hasClass('prd-qty') ) $find = $(this).closest('tr').find('.prd-price');

  if ( $(this).val().length > 1 && $(this).closest('tr').find('.order_excepted').val() == 0) {
    if ( e.keyCode == 13 ) time = 0;
    setTimeout(() => {
      if ( $(this).val() == '' ) $(this).val(0);
      subtotal = parseFloat($(this).val()) * parseFloat($find.val());
      $(this).closest('tr').find('.request-subtotal').val(subtotal.toFixed(2));
      inventoryAmount();
    }, time);
  }
}).on('change', '.packaging-status', function() {
  if ( $('.package').length ) {
    if ( currentStepIndex >= $('.packaging-status option').index($('.packaging-status option:selected')) )  {
      $('.package').removeAttr('name');
    } else {
      Array.from($('.package')).forEach((v) => {
        if ( typeof $(v).data('name') != 'undefined' ) {
          $(v).attr('name', $(v).data('name'));
        }
      });
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
});

function inventoryAmount() {
  if ( $(".request-subtotal").length ) {
    let inventory_amount = 0;
    Array.from($(".request-subtotal")).forEach((v) => {
      if ($(v).attr('type') == 'text') {
        inventory_amount += parseFloat($(v).val());
      }
    });

    inventory_amount = inventory_amount.toFixed(2);

    $('input[name="order[inventory_fixed_amount]"]').val(inventory_amount);
    
    if ( $(".inventory_fixed_amount").length ) {
      $(".inventory_fixed_amount").text(inventory_amount);
    }
  }
}
