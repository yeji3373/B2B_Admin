$(document).on('click', '.brand-btn', function() {
  let control = $(this).data('control');
  let form = $(this).closest('form');

  // console.log('select ', select);
  if ( typeof control != 'undefined' ) {
    if ( control == 'edit') {
      if ( $(this).closest('form').find('[name="brand_opt[supply_rate_based]"]').val() == '1' ) {
        console.log( $(this).closest('form').find('[name="brand_opt[supply_rate_based]"]').val(), "== 1");
        if ( $(this).closest('form').find('[name="brand_opt[supply_rate_by_brand]"]').val() == '' ) {
          return false;
        }
      }
      
      // console.log($(this).parent().find('input[type=hidden].margin_rate'));
      // // if ( form.find('[name="margin_rate_control"]').val() == false 
      // //   && form.find('[name=supply_rate_control]').val() == false
      // //   && form.find('[name=brand_control]').val() == false ) {
      //     return false;
      // //   }
    } else if ( control == 'regist') {
      form.attr('action', `/brand/regist`).submit();
    } else {
      return false;
    }
  }
// }).on('change', '.supply_rate_based', function() {
}).on('change', '[name$="[supply_rate_based]"]', function() {
  let target = $(this).closest('form').find('[name="brand_opt[supply_rate_by_brand]"]');
  if ( target.length ) {
    target.prop('disabled', !Boolean(parseInt($(this).val())));
  }
}).on('change', '.margin_section', function(e) {  
  console.log($(this).val());
  console.log($(this).data('target').length);
  let target = $(this).data('target');

  if ( typeof $(this).data('target') != undefined ) {    
    target = $(this).closest(".form-check").find(`[name='${target}']`);
    
    if ( target.length ) {
      // if ( target.prop('disabled') ) {
        target.prop('disabled', !target.prop('disabled'));
      // }
    }
  }
}).on('keypress', 'form', function(e) {
  if ( e.keyCode == 13 ) return false;
});