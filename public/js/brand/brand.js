$(document).on('click', '.brand-btn', function() {
  let control = $(this).data('control');
  let form = $(this).closest('form');

  // console.log('select ', select);
  if ( typeof control != 'undefined' ) {
    if ( control == 'edit') {
      if ( form.find('[name="margin_rate_control"]').val() == false 
        && form.find('[name=supply_rate_control]').val() == false
        && form.find('[name=brand_control]').val() == false ) {
          return false;
        }
      // let margin_rate_control = form.find('[name=supply_rate_control]');
      // if ( margin_rate_control.val() == true ) {
      //   form.find('input[type=hidden].margin_rate').prop('disabled', true);
      // }
      // if ( form.find('.supply_rate_by_brand').length > 0 ) {
      //   if ( form.find('.supply_rate_by_brand').data('old') != form.find('.supply_rate_by_brand').val() ) {
      //     price_control.val(true);
      //   }
      // }
      // form = $(this).siblings('form');
      // // data = $(this).closest('tr').find('td input').serialize();
      // data = $(this).closest('tr').find('td input');

      // if ( data != '' ) {
      //   form.append(data).submit();
      // }
    } else if ( control == 'regist') {
      form.attr('action', `/brand/regist`).submit();
    } else {
      // if ( select.prop('disabled') === true ) {
      //   console.log("has");
      //   input.attr('type', 'hidden').prop('disabled', true);
      //   select.removeClass('d-none').prop('disabled', false);
      //   $(this).text('추가')
      // } else {
      //   console.log("!has ", select);
      //   input.attr('type', 'text').prop('disabled', false);
      //   select.addClass('d-none').prop('disabled', true);
      //   $(this).text('추가 취소');
      // }

      return false;
    }
  }
// }).on('change', '.supply_rate_based', function() {
}).on('change', '[name$="[supply_rate_based]"]', function() {
  if ( parseInt($(this).val()) == 1 ) {
    // $(this).closest('form').find('[name=supply_rate_control]').val(1);
    $(this).closest('.brand-list-body').find('.supply_rate_by_brand').prop('disabled', false).prop('required', true);
  } else {
    // $(this).closest('form').find('[name=supply_rate_control]').val(0);
    $(this).closest('.brand-list-body').find('.supply_rate_by_brand').prop('disabled', true);
  }

  if ( parseInt($(this).val()) == $(this).data('old') ) {
    $(this).closest('form').find('[name=supply_rate_control]').val(0);
    console.log("같음 ");
    // $(this).closest('.brand-list-body').find('.supply_rate_by_brand').prop('disabled', true);
  } else {
    $(this).closest('form').find('[name=supply_rate_control]').val(1);
    console.log("다름 ");
  }
  console.log($(this).closest('form').find('[name=supply_rate_control]').val());
}).on('change', '.supply_rate_by_brand', function() {
  if ( $(this).data('old') != $(this).val() ) {
    $(this).closest('form').find('[name=supply_rate_control]').val(1);
  } else $(this).closest('form').find('[name=supply_rate_control]').val(0);
}).on('change', '.margin_section', function(e) {
  if ( $(this).data('old') == $(this).val() ) {
    $(this).closest('form').find('[name=margin_rate_control]').val(0);
    $(this).closest('form').find('input[type=hidden].margin_rate').prop('disabled', true);

    if ( parseInt($(this).val()) == 1 ) {
      $(this).closest('form').find('input[type=hidden].margin_rate').prop('disabled', false);  
    }
  } else { 
    $(this).closest('form').find('[name=margin_rate_control]').val(1);
    $(this).closest('form').find('input[type=hidden].margin_rate').prop('disabled', false);
  }
}).on('change', '[name$="[brand_name]"], [name$="[available]"], [name$="[own-brand]"]', function() {
  if ( $(this).val() != $(this).data('old') ) {
    $(this).closest('form').find('[name=brand_control]').val(1);
  } else {
    if ( parseInt($(this).val()) == 0 ) $(this).closest('form').find('[name=brand_control]').val(0);
  } 
}).on('change', '[name$="[margin_rate]"]', function() {
  if ( $(this).val() != $(this).data('old') ) {
    $(this).closest('form').find('[name=margin_rate_control]').val(1);
  } else $(this).closest('form').find('[name=margin_rate_control]').val(0);
}).on('keypress', 'form', function(e) {
  if ( e.keyCode == 13 ) return false;
});