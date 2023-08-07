$(document).ready(function() {
  if ( $(".inventory-detail-container .request-subtotal").length ) {
    Array.from($(".inventory-detail-container .request-subtotal")).forEach((v) => {
      $(v).val
    });
  }
}).on('click', '.inventory-detail-container .temp-save', function(e) {
  if ( $('input[name="order[inventory_fixed_amount]"]').val() == 0 ) {
    Array.from($(".detail_items")).forEach((v, i) => {
      Array.from($(v).find('input')).forEach((value, index) => {
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
    return false;
  }
  
  // $('.inventory-detail-container form').submit();
}).on('change', '.inventory-detail-container input[type=checkbox].value-change', function(){
  let temp = 0, $target = null;
  if ( typeof $(this).data('cancelTarget') != 'undefined'
      && typeof $(this).data('cancelValue') != 'undefined' 
      && typeof $(this).data('cancelParent') != 'undefined' ) {
    if ( $(this).closest($(this).data('cancelParent')).find($(this).data('cancelTarget')).length ) {
      $target = $(this).closest($(this).data('cancelParent')).find($(this).data('cancelTarget'));
      temp = $target.data('temp');

      if ( $(this).val() == 1 ) { 
        $target.val($(this).data('cancelValue'));
      } else $target.val(temp);
    }
  }
});
