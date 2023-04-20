<?= view('Auth\Views\_notifications') ?>
<?php if ( !empty($brands) ) : ?>
<div class='d-flex flex-column flew-wrap w-100 border border-dark border-bottom-0 brand-list'>
  <div class='d-flex flex-row w-100 brand-list-header border border-0 border-dark border-bottom'>
    <div class='list-items border border-dark border-0 border-end'>No</div>
    <div class='list-items border border-dark border-0 border-end w-30p'>브랜드명</div>
    <div class='list-items border border-dark border-0 border-end w-17p'>자사 브랜드</div>
    <div class='list-items border border-dark border-0 border-end w-18p'>공급률 적용 여부</div>
    <div class='list-items border border-dark border-0 border-end w-10p'>공급률</div>
    <div class='list-items border border-dark border-0 border-end w-20p'>마진율</div>
    <div class='list-items border border-dark border-0 border-end w-15p'>브랜드 사용 여부</div>
    <div class='list-items w-7p'></div>
  </div>
  <?php foreach ($brands as $i => $brand) : ?>
  <form class='m-0' action='<?=base_url('brand/edit')?>' method='post'>
  <input type='hidden' name='margin_rate_control' value='0'>
  <input type='hidden' name='supply_rate_control' value='0'>
  <input type='hidden' name='brand_control' value='0'>
  <div class='d-flex flex-row w-100 brand-list-body'>
    <div class='border border-dark border-0 border-bottom border-end list-items'>
      <input type='hidden' name='brand_id' value='<?=$brand['brand_id']?>'>
      <?=$brand['brand_id']?>
    </div>
    <div class='border border-dark border-0 border-bottom border-end text-start list-items w-30p'>
      <input type='text'
        name='brand[<?=$brand['brand_id']?>][brand_name]'
        data-old='<?=$brand['brand_name']?>'
        class='form-control form-control-sm w-100'
        placeholder='브랜드 영문 이름 입력'
        required
        value='<?=!empty($brand['brand_name']) ? $brand['brand_name'] : ''?>'>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-17p'>
      <div class='form-check form-check-inline my-1 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[<?=$brand['brand_id']?>][own-brand]'
            data-old='<?=$brand['own_brand']?>'
            type='radio'
            value='1'
            <?=$brand['own_brand'] == 1 ? 'checked' : ''?>>
            자사 브랜드
        </label>
      </div>
      <div class='form-check form-check-inline my-1 me-0 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[<?=$brand['brand_id']?>][own-brand]'
            type='radio'
            value='0'
            <?=$brand['own_brand'] == 0 ? 'checked' : ''?>>
            타사 브랜드
        </label>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-18p'>
      <div class='form-check form-check-inline my-1 min-height-auto'>
        <input type='hidden' name='brand_opt[<?=$brand['brand_id']?>][idx]' value='<?=$brand['brand_opt_idx']?>'>
        <label class='form-check-label'>
          <input class='form-check-input supply_rate_based'
            name='brand_opt[<?=$brand['brand_id']?>][supply_rate_based]'
            data-old='<?=$brand['supply_rate_based']?>'
            type='radio'
            value='1'
            <?=$brand['supply_rate_based'] == 1 ? 'checked' : ''?>>
            공급률 적용
        </label>
      </div>
      <div class='form-check form-check-inline my-1 me-0 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input supply_rate_based'
            name='brand_opt[<?=$brand['brand_id']?>][supply_rate_based]'
            data-old='<?=$brand['supply_rate_based']?>'
            type='radio'
            value='0'
            <?=(empty($brand['supply_rate_based'])) || !isset($brand) ? 'checked' : ''?>>
            공급률 미적용
        </label>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-10p'>
      <div class='d-flex flex-row align-items-end'>
        <input type='text'
          name='brand_opt[<?=$brand['brand_id']?>][supply_rate_by_brand]'
          class='form-control form-control-sm me-1 w-75 supply_rate_by_brand'
          data-old='<?=!empty($brand['supply_rate_by_brand']) ? ($brand['supply_rate_by_brand'] * 100) : ''?>'
          placeholder='20'
          pattern='[0-9]{1,3}([\.][0-9]{0,2})?'
          <?=$brand['supply_rate_based'] == 1 ? 'required': 'disabled'?>
          value='<?=!empty($brand['supply_rate_by_brand']) ? ($brand['supply_rate_by_brand'] * 100) : ''?>'>
          %
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-20p'>
      <div class='d-flex flex-row'>
      <?php if ( !empty($margin) ) : 
        foreach ( $margin as $j => $m ) : ?>
        <div class='form-check d-flex flex-row align-items-center'>
          <input type='hidden'
                class='margin_rate'
                name='margin_rate[<?=$j?>][idx]' 
                value='<?=!empty($brand['marginRate']) && isset($brand['marginRate'][$j]['idx']) ? $brand['marginRate'][$j]['idx'] : '' ?>'
                <?=!empty($brand['marginRate']) && ( isset($brand['marginRate'][$j]['idx']) && $brand['marginRate'][$j]['available'] == 1 ) ? '' : 'disabled' ?> >
          <input type='hidden' 
                class='margin_rate'
                name='margin_rate[<?=$j?>][brand_id]'
                value='<?=!empty($brand['marginRate']) && isset($brand['marginRate'][$j]['brand_id']) ? $brand['marginRate'][$j]['brand_id'] : $brand['brand_id']?>'
                <?=!empty($brand['marginRate']) && ( isset($brand['marginRate'][$j]['idx']) && $brand['marginRate'][$j]['available'] == 1 ) ? '' : 'disabled' ?>>
          <input type='hidden'
                class='margin_rate'
                name='margin_rate[<?=$j?>][margin_idx]'
                value='<?=$m['idx']?>'
                <?=!empty($brand['marginRate']) && ( isset($brand['marginRate'][$j]['idx']) && $brand['marginRate'][$j]['available'] == 1 ) ? '' : 'disabled' ?>>
          <label class='form-check-label d-flex flex-row align-items-center justify-content-center'>
            <input type='checkbox' 
                  class='me-1 value-change margin_section'
                  data-old='<?=!empty($brand['marginRate']) ? $brand['marginRate'][$j]['available'] : '' ?>'
                  data-find-parent='div.form-check'
                  data-find-target='[name="margin_rate[<?=$j?>][margin_rate]"]'
                  data-condition='[{"condition": "0", "action": "disabled", "value": true}, {"condition": "1", "action": "disabled", "value": false}, {"condition": "0", "action": "required", "value": false}, {"condition": "1", "action": "required", "value": true}]'
                  name='margin_rate[<?=$j?>][available]'
                  value='<?=(!empty($brand['marginRate']) && isset($brand['marginRate'][$j]['available'])) ? $brand['marginRate'][$j]['available'] : 0 ?>'
                  <?=!empty($brand['marginRate']) && (isset($brand['marginRate'][$j]['margin_idx']) && $brand['marginRate'][$j]['available'] == 1 ) && $brand['marginRate'][$j]['margin_idx'] == $m['idx'] ? 'checked' : '' ?>>
            <?=$m['margin_section']?>
          </label>
          <input type='text'
            class='form-control form-control-sm w-50 mx-1 margin_rate' 
            name='margin_rate[<?=$j?>][margin_rate]'
            data-old='<?=!empty($brand['marginRate']) ? ($brand['marginRate'][$j]['margin_rate'] * 100) : '' ?>'
            value='<?=!empty($brand['marginRate']) && isset($brand['marginRate'][$j]['margin_rate']) ? $brand['marginRate'][$j]['margin_rate'] * 100 : ''?>'
            <?=!empty($brand['marginRate']) && (isset($brand['marginRate'][$j]['margin_rate']) && $brand['marginRate'][$j]['available'] == 1) ? '' : 'disabled'?>
          >%
        </div>
      <?php endforeach;
      endif;?>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-15p'>
      <div class='form-check form-check-inline my-1 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[<?=$brand['brand_id']?>][available]'
            data-old='<?=$brand['available']?>'
            type='radio'
            value='1'
            required
            <?=$brand['available'] == 1 ? 'checked' : ''?>>
            유효
        </label>
      </div>
      <div class='form-check form-check-inline my-1 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[<?=$brand['brand_id']?>][available]'
            data-old='<?=$brand['available']?>'
            type='radio'
            value='0'
            required
            <?=($brand['available'] == 0 ) ? 'checked' : ''?>>
            유효하지 않음
        </label>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom list-items w-7p'>
      <button tyle='submit' class='btn btn-sm btn-secondary ms-2 brand-btn' data-control='edit'>수정</button>
    </div>
  </div>
  </form>
  <?php endforeach; ?>
  <form action='<?=base_url('brand/regist')?>' method='post'>
  <div class='d-flex flex-row w-100 brand-list-body'>
    <div class='border border-dark border-0 border-bottom border-end list-items'>
      -
    </div>
    <div class='border border-dark border-0 border-bottom border-end text-start list-items w-30p'>
      <input type='text'
        name='brand[brand_name]'
        class='form-control form-control-sm w-100'
        placeholder='브랜드 영문 이름 입력'
        required>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-17p'>
      <div class='form-check form-check-inline my-1 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[own-brand]'
            type='radio'
            value='1'>
            자사 브랜드
        </label>
      </div>
      <div class='form-check form-check-inline my-1 me-0 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[own-brand]'
            type='radio'
            value='0'
            checked>
            타사 브랜드
        </label>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-18p'>
      <div class='form-check form-check-inline my-1 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input supply_rate_based'
            name='brand[supply_rate_based]'
            type='radio'
            value='1'>
            공급률 적용
        </label>
      </div>
      <div class='form-check form-check-inline my-1 me-0 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input supply_rate_based'
            name='brand[supply_rate_based]'
            type='radio'
            value='0'
            checked>
            공급률 미적용
        </label>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-10p'>
      <div class='d-flex flex-row align-items-end'>
        <input type='text'
          name='brand[supply_rate_by_brand]'
          class='form-control form-control-sm me-1 w-75 supply_rate_by_brand'
          placeholder='20'
          pattern='[0-9]{1,3}([\.][0-9]{0,2})?'
          <?=$brand['supply_rate_based'] == 1 ? 'required': 'disabled'?>>
          %
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-20p'>
      <div class='d-flex flex-row'>
      <?php if ( !empty($margin) ) : 
        foreach ( $margin as $j => $m ) : ?>
        <input type='hidden' 
              name='margin_rate[][idx]'>
        <input type='hidden' 
              name='margin_rate[][brand_id]'>
        <input type='hidden'
              name='margin_rate[][margin_idx]'>
        <div class='form-check d-flex flex-row align-items-center'>
          <label class='form-check-label d-flex flex-row align-items-center justify-content-center'>
            <input type='checkbox' 
                  class='me-1 value-change'
                  data-find-parent='div.form-check'
                  data-find-target='[name="margin_rate[][margin_rate]"]'
                  data-condition='[{"condition": "0", "action": "disabled", "value": true}, {"condition": "1", "action": "disabled", "value": false}]'
                  name='margin_rate[][available]'>
            <?=$m['margin_section']?>
          </label>
          <input class='form-control form-control-sm w-50 mx-1' 
            type='text'
            name='margin_rate[][margin_rate]'
            disabled
          >%
        </div>
      <?php endforeach;
      endif;?>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom border-end list-items w-15p'>
      <div class='form-check form-check-inline my-1 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[available]'
            type='radio'
            value='1'
            required>
            유효
        </label>
      </div>
      <div class='form-check form-check-inline my-1 me-0 min-height-auto'>
        <label class='form-check-label'>
          <input class='form-check-input'
            name='brand[available]'
            type='radio'
            value='0'
            required>
            유효하지 않음
        </label>
      </div>
    </div>
    <div class='border border-dark border-0 border-bottom list-items w-7p'>
      <button tyle='submit' class='btn btn-sm btn-danger ms-2 brand-btn' data-control='edit'>등록</button>
    </div>
  </div>
  </form>
</div>
<?php endif;?>
