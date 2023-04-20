<form action='<?=base_url('brand/regist')?>' data-edit-action='<?=base_url('brand/edit')?>' method='post'>
  <input type='hidden' name='control'>
  <input type='hidden' name='brand_id' value='<?=isset($brand) && $brand['brand_id'] ? $brand['brand_id'] : ''?>'>
  <table class='w-50 mb-4'>
    <tbody>
      <tr>
        <th class='w-25 border border-dark border-0 border-end border-bottom p-2'>브랜드</th>
        <td class='border border-dark border-0 border-bottom text-start p-2'>
          <div class='d-flex flex-row'>
            <select 
              class='form-control form-control-sm text-uppercase brand_sel w-50'>
            <?php if ( !empty($brands) ) :
            foreach( $brands as $_brand ) : ?>
              <option class='text-uppercase' 
                value='<?=$_brand['brand_id']?>'
                <?=isset($brand['brand_id']) && ($_brand['brand_id'] == $brand['brand_id']) ? 'selected': ''?>>
                <?=$_brand['brand_name']?>
              </option>
            <?php endforeach;
            endif; ?>
            </select>
            <div class='btn btn-sm btn-secondary ms-2 brand-btn' data-control='edit'>수정 브랜드 선택</div>
          </div>          
        </td>
      </tr>
      <tr>
        <th class='w-25 border border-dark border-0 border-end border-bottom p-2'>브랜드명</th>
        <td class='border border-dark border-0 border-bottom text-start p-2'>
          <input type='text'
            name='brand_name'
            class='form-control form-control-sm w-75'
            placeholder='브랜드 영문 이름 입력'
            required
            value='<?=isset($brand) && !empty($brand['brand_name']) ? $brand['brand_name'] : ''?>'>
        </td>
      </tr>
      <tr>
        <th class='w-25 border border-dark border-0 border-end border-bottom p-2'>자사 브랜드</th>
        <td class='border border-dark border-0 border-bottom text-start p-2'>
          <div class='form-check form-check-inline my-1 min-height-auto'>
            <label class='form-check-label fs-inherit fw-normal'>
              <input class='form-check-input'
                name='own-brand'
                type='radio'
                value='1'
                <?=isset($brand) && $brand['own_brand'] == 1 ? 'checked' : ''?>>
                자사 브랜드
            </label>
          </div>
          <div class='form-check form-check-inline my-1 min-height-auto'>
            <label class='form-check-label fs-inherit fw-normal'>
              <input class='form-check-input'
                name='own-brand'
                type='radio'
                value='0'
                <?=(isset($brand) && empty($brand['own_brand'])) || !isset($brand) ? 'checked' : ''?>>
                타사 브랜드
            </label>
          </div>
        </td>
      </tr>
      <tr>
        <th class='w-25 border border-dark border-0 border-end border-bottom p-2'>공급률 적용</th>
        <td class='border border-dark border-0 border-bottom text-start p-2'>
          <div class='form-check form-check-inline my-1 min-height-auto'>
            <label class='form-check-label fs-inherit fw-normal'>
              <input class='form-check-input'
                name='supply_rate_based'
                type='radio'
                value='1'
                <?=isset($brand) && $brand['supply_rate_based'] == 1 ? 'checked' : ''?>>
                공급률 적용
            </label>
          </div>
          <div class='form-check form-check-inline my-1 min-height-auto'>
            <label class='form-check-label fs-inherit fw-normal'>
              <input class='form-check-input'
                name='supply_rate_based'
                type='radio'
                value='0'
                <?=(isset($brand) && empty($brand['supply_rate_based'])) || !isset($brand) ? 'checked' : ''?>>
                공급률 미적용
            </label>
          </div>
        </td>
      </tr>
      <tr>
        <th class='w-25 border border-dark border-0 border-end border-bottom p-2'>공급률</th>
        <td class='border border-dark border-0 border-bottom text-start p-2'>
          <div class='d-flex flex-row align-items-end'>
          <input type='text'
            name='supply_rate_by_brand'
            class='form-control form-control-sm me-1 w-25'
            placeholder='20'
            pattern='[0-9]{1,3}([\.][0-9]{0,2})?'
            <?=isset($brand) && $brand['supply_rate_based'] == 1 ? 'required': 'disabled'?>
            value='<?=isset($brand) && !empty($brand['supply_rate_by_brand']) ? $brand['supply_rate_by_brand'] : ''?>'>
            %
          </div>
        </td>
      </tr>
      <tr>
        <th class='w-25 border border-dark border-0 border-end border-bottom p-2'>브랜드 사용여부</th>
        <td class='border border-dark border-0 border-bottom text-start p-2'>
          <div class='form-check form-check-inline my-1 min-height-auto'>
            <label class='form-check-label fs-inherit fw-normal'>
              <input class='form-check-input'
                name='available'
                type='radio'
                value='1'
                required
                <?=isset($brand) && $brand['available'] == 1 ? 'checked' : ''?>>
                유효
            </label>
          </div>
          <div class='form-check form-check-inline my-1 min-height-auto'>
            <label class='form-check-label fs-inherit fw-normal'>
              <input class='form-check-input'
                name='available'
                type='radio'
                value='0'
                required
                <?=(isset($brand) && $brand['available'] == 0 ) ? 'checked' : ''?>>
                유효하지 않음
            </label>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
  <button type='submit' class='btn btn-secondary'><?=isset($edit) ? '수정' : '등록'?></button>
</form>