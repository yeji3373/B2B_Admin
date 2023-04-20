$(document).on('change', '.buyerManager', function() {
  console.log($(this).data('buyer'));
  getData('/Buyer/editManager', 
          {'type': 1,  
            'id': $(this).data('buyer'), 
            'manager_id': $(this).val()
          });
}).on('change', '[name=cRate_idx]', function() {
  let data = $(this).find('option:selected').data();
  $('[name=exchange_rate]').val(data.exchangerate);
  $('[name=currency_idx]').val(data.currencyidx);
});
