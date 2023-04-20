<?php if ( !empty($data) ) : 
  foreach ($data as $d ) : ?>?>
<div class='form-check d-flex flex-row align-items-center'>
  <label class='form-check-label d-flex flex-row align-items-center justify-content-center'>
    <input type='checkbox' 
          class='me-1'
          name='margin_rate[][margin_idx]'>
    a
  </label>
  <input class='form-control form-control-sm w-50 mx-1' type='text' name='margin_rate[][margin_rate]'>%
</div>
<?php endforeach;
endif; ?>