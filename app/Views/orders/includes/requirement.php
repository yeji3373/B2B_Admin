<?php if( !empty($requirement) ) :
  foreach ($requirement AS $j => $require ) : ?>
  <?php if ( !$require['requirement_check'] ) : ?>
  <div class='d-flex flex-column mx-1' style='width: 10rem;'>
    <div class='text-start d-flex flex-row flex-nowrap justify-content-between'>
      <p class='fw-bold' 
            data-toggle='tooltip'
            data-placement='top'
            title='<?=$require['requirement_detail']?>'>
        <?=$require['requirement_kr']?>
      </p>
      <label class='d-flex flex-row' style='font-size: 0.7rem;'>
        <input class='value-change me-1' 
              type='checkbox' 
              name='[requirement][<?=$j?>][requirement_check]'
              value='<?=$require['requirement_check']?>'
              <?=$require['requirement_check'] == true ? 'checked' : ''?>>
        확인
      </label>
    </div>
    <textarea class='w-100' 
          name='[requirement][<?=$j?>][requirement_reply]'
          placeholder='<?=$require['requirement_detail']?>'
          row='2'
          style='height: 2rem;'></textarea>
  </div>
  <?php endif; ?>
<?php endforeach;
endif; ?>