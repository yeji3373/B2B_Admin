$(document).ready(function() {
  if( $('.group_choose').val() == "new_group" ) {
    $('.group_name').removeAttr('disabled');
  } else {
    $('.group_name').attr('disabled', 'disabled');
  }

  if ( $(".single-product-regist-edit-container [name='product_price[not_calculating_margin]']").length ) {
    if ( $(".single-product-regist-edit-container [name='product_price[not_calculating_margin]']").val() == '1' ) {
      $('.supply-price-input').prop('disabled', false);
    }
  }
});
$(document).on('focusin', '[name="product_price[supply_price]"]', function() {
  let retail_price = $('[name="product_price[retail_price]"]');
  let product_price = $('[name="product_price[supply_price]"]');
  let supply_rate = $('[name="product_price[supply_rate]"]');
  let calc_product_price = 0;
  if ( retail_price.val() != '' && retail_price.val() > 0 ) {
    if (supply_rate.val() != '' && supply_rate.val() > 0 ) {
      calc_product_price = Math.round(retail_price.val() * (supply_rate.val() / 100));
      product_price.val(calc_product_price);
    }
  }
}).on('keyup', '[name="product_price[supply_rate]"]', function() {
  let retail_price = $('[name="product_price[retail_price]"]');
  setTimeout(() => {
    if ( $(this).val() != '' && $(this).val() > 0 ) {
      if (retail_price.val() != '' && retail_price.val() > 0 ) {
        $('[name="product_price[supply_price]"]').val(Math.round(retail_price.val() * ($(this).val() / 100)));
      }
    } else $('[name="product_price[supply_price]"]').val(parseFloat($('[name="product_price[supply_price]"]').data('old')));
  }, 500);
}).on('change', '[name="product_price[supply_rate_applied]"]', function() {
  if ($(this).is(":checked") === true ) {
    // if ( $('[name="product_price[not_calculating_margin]"]').val() == 1 ) {
    //   alert('공급가 직접 입력과 함께 적용되지 않습니다.');
    //   $(this).val(0).prop('checked', false);
    //   return false;
    // }

    if ( $('[name="product_price[retail_price]"]').val() == '' 
      || $('[name="product_price[retail_price]"]').val() <= 0 ) {
      alert('소비자가 먼저 입력 후에 진행해주세요');
      $(this).prop('checked', false);
      return;
    }
    
    if ( $(".supplay_rate_tr").hasClass("d-none") ) {
      $(".supplay_rate_tr").removeClass('d-none');
      $("[name='product_price[supply_rate]']").prop({'disabled': false, 'required': true});
    }
  } else {
    let supply_rate = $('.applied_rate').text() != '' ? (parseFloat($('.applied_rate').text()) / 100) : 0;
    let retail_rate = $('[name="product_price[retail_price]"]').val() != '' ? parseFloat($('[name="product_price[retail_price]"]').val()) : 0;
    let old = '';

    // if ( $("[name='product_price[supply_price]']").data('old') != '' ) {
    //   old = parseFloat($("[name='product_price[supply_price]']").data('old'));
    // }
    old = (retail_rate * supply_rate);

    $(".supplay_rate_tr").addClass('d-none');
    $("[name='product_price[supply_rate]']").prop({'disabled': true, 'required': false}).val('');
    $("[name='product_price[supply_price]']").val(old);
  }
}).on('change', '[name="product[box]"]', function() {
  if ( $(this).is(":checked") === true ) {
    if ( typeof $(this).data('target') !== 'undefined' ) {
      
    }
  }
}).on('change', '[name="brand_id"], [name="product[brand_id]"]', function() {
  if ( $(this).find('option:selected').data('link') == 1 ) {
    if ( $(this).find('option:selected').val().includes('http') ) {
      location.href = $(this).find('option:selected').val();
    }
  }

  if ( typeof $(this).find('option:selected').data('supplyApplied') != 'undefined' ) {
    if ( $(this).find('option:selected').data('supplyApplied') == 1 ) {
      // if ( $('[name="product_price[supply_rate_applied]"]') ) {
      //   if ( !$('[name="product_price[supply_rate_applied]"]').is(":checked") ) {
      //     $('[name="product_price[supply_rate_applied]"]').click();
      //     $('[name="product_price[supply_rate]"]').val($(this).find('option:selected').data('supplyRate'));
      //   }
      // }
      $('[name="product_price[supply_rate_applied]"]').prop('disabled', false);

      if ( $(this).siblings().find('.applied_rate') ) {
        if ( $(this).siblings().hasClass('d-none') ) {
          $(this).siblings().removeClass('d-none');
          $(this).siblings().find('.applied_rate').text($(this).find('option:selected').data('supplyRate'));
        } else {
          $(this).siblings().addClass('d-none');
          $(this).siblings().find('.applied_rate').text('');
        }
      }
    } else {
      $('[name="product_price[supply_rate_applied]"]').prop('disabled', true);

      if ( $(this).siblings().find('.applied_rate') ) {
        if ( ! $(this).siblings().hasClass('d-none') ) {
          $(this).siblings().addClass('d-none');
          $(this).siblings().find('.applied_rate').text('');
        }
      }

      // if ( $('[name="product_price[supply_rate_applied]"]') ) {
      //   if ( $('[name="product_price[supply_rate_applied]"]').is(":checked") ) {
      //     $('[name="product_price[supply_rate_applied]"]').click();
      //     $('[name="product_price[supply_rate]"]').val('');
      //   }
      // }
    }
  }  
}).on('change', '[name="product[box]"]', function() {
  console.log($(this).closest('td'));
  $(this).closest('td').find('input[type=text]').prop('disabled', true);
  $(this).closest('p').siblings().find('input[type=text]').prop('disabled', false);
}).on('click', '.product-csv-btn', function(e) {
  let action = '/product/exportData';
  if ( $("[name=brand_id] option:selected").val() == '' ) {
    alert("brand check please");
    return false;
  }
  $(this).closest('form').attr('action', action);
}).on('click', '.attach-btn', function() {
  let action = '/product/attachProduct';
  if ( $("[name=brand_id] option:selected").val() == '' ) {
    alert("brand check please");
    return false;
  }
  
  if ( $('.prd-include-chk:checked').length <= 0 ) {
    alert("옵션을 선택해 주세요.");
    return false;
  }
  $(this).closest('form').attr('action', action);
}).on('keyup', '[name="product_price[][supply_rate]"]', function() {
  if ( $(this).val() > 0 && $(this).val() <= 100 ) {
    $(this).closest('tr').addClass('checked true');
  } else {
    if ( $(this).val() > 100 ) alert('100%로 입력');
  }
}).on('change', '.edit-check', function() {
  if ($('.edit-check:checked').length > 0 )  {
    if ( $("form.form-edit .edit-btn").has("d-none") ) {
      $("form.form-edit .edit-btn").removeClass("d-none");
    }
  } else $("form.form-edit .edit-btn").addClass("d-none");

  if ( $(this).is(":checked") ) {
    $(this).closest('tr').find('.ids input[type=hidden]').prop('disabled', false);
  } else {
    $(this).closest('tr').find('.ids input[type=hidden]').prop('disabled', true);
  }
}).on('click', 'form.form-edit .edit-btn', function() {
//   let form = $('form.form-edit');
  
//   if ( form.find('input[type=checkbox].edit-check:checked').length > 0 ) {
//     $('form.form-edit').submit();
//   } 

//   return false;
//   // form.append($('table tr.checked').find('input'));
}).on('keyup', 'form.form-edit', function(e) {
  
}).on('change', '[name="product_price[not_calculating_margin]"]', function() {
  if ( $(this).val() == 1 ) {
    // if ( $('[name="product_price[supply_price]"]').val() == $('[name="product_price[supply_price]"]').data('old') ) {
    //   if ( confirm('가격 변경이 없습니다. 그래도 진행하시겠습니까?') ) {
    //     console.log("no changed");
    //   } else console.log("changed");
    // }

    // if ( $('[name="supply_rate_based"]').length > 0 ) {
    //   if ( $('[name="supply_rate_based"]').val() == 1 ) {
    //     alert('공급률과 동시에 적용되지 않습니다.');
    //     $(this).val(0).prop('checked', false);
    //     return false;
    //   }
    // }

    // if ( $('[name="product_price[supply_rate_applied]"]').val() == 1 ) {
    //   alert('상품별 공급률과 동시에 적용되지 않습니다.');
    //   $(this).val(0).prop('checked', false);
    //   return false;
    // }

    $('[name="product_price[supply_price]"]').prop('disabled', true);
    $('.supply-price-input').prop({'disabled': false, 'required': true});
  } else {
    $('[name="product_price[supply_price]"]').prop('disabled', false);
    $('.supply-price-input').prop({'disabled': true, 'required': false});
  }
}).on('keyup', '.supply-price-input', function() {

}).on('change', '.group_choose', function() {
  if($(this).val() != ''){
    if($(this).val() == "new_group") {
      $('.group_name').removeAttr('disabled');
    }else{
      $('.group_name').val($('.group_choose option:checked').text().trim());
      $('.group_name').attr('disabled', 'disabled');
    }
  }
});