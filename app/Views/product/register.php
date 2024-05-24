<main id="productInputMain">
  <?= view('Auth\Views\_notifications') ?>
  <?php $validation = \Config\Services::validation(); ?>
  
  <div class='d-grid grid-half' >
  <?=view('/product/single')?>

  <div class='ms-5 <?=(isset($edit) && $edit === true) ? 'd-none' : ''?>'>
    <h6><?=lang('Product.bulkInput')?></h6>
    <form action="<?=site_url('product/attachProduct')?>" method="post" enctype="multipart/form-data">
      <table class='w-100'>
        <tbody>
          <tr>
            <th class='border border-0 border-dark border-bottom border-end w-25'>
              <?=lang('Product.brand')?>
            </th>
            <td class='text-start'>
              <div class='d-flex flex-row'>
                <?php 
                if ( !empty($brands) ) : ?>
                <select name='brand_id' class='form-select form-select-sm w-50'>
                  <option value selected><?=lang('Product.brandChoose')?></option>
                  <option value='<?=base_url('brand')?>' data-link='1'>브랜드 등록</option>
                  <?php foreach ( $brands as $brand) : ?>
                    <option value="<?=$brand['brand_id']?>" 
                      data-supply-applied='<?=$brand['supply_rate_based']?>' 
                      data-supply-rate='<?=($brand['supply_rate_by_brand'] * 100)?>'>
                      <?=stripslashes(htmlspecialchars(strtoupper($brand['brand_name'])))?>
                    </option>
                  <?php endforeach ?>
                </select>
                <?php endif; ?>
                <div class='d-flex flex-row flex-wrap align-items-center ms-4 px-2 text-bg-danger d-none '>
                  <span class='me-1'>공급률</span>
                  <span class='applied_rate'></span>
                  <span>% 적용중</span>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <th class='border border-0 border-dark border-bottom border-end w-25'>옵션</th>
            <td class='text-start'>
              <div class='d-flex flex-row'>
                <div class='form-check mt-2 me-2'>
                  <label class='form-check-label fs-inherit fw-light'>
                    <input class='form-check-input prd-include-chk value-change' name='prd-include' type='checkbox' value='0'>
                    <span>제품 정보</span>
                  </label>
                </div>
                <div class='form-check mt-2 me-2 update'>
                  <label class='form-check-label fs-inherit fw-light'>
                    <input class='form-check-input prd-include-chk value-change' name='prd-price-include' type='checkbox' value='0'>
                    <span>제품 가격</span>
                  </label>
                </div>
                <div class='form-check mt-2 update'>
                  <label class='form-check-label fs-inherit fw-light'>
                    <input class='form-check-input prd-include-chk value-change' name='prd-moq-include' type='checkbox' value='0'>
                    <span>제품 MOQ/SPQ</span>
                  </label>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <th class='border border-0 border-dark border-end w-25'><?=lang('Product.fileUpload')?></th>
            <td>
              <input type="file" name="file" class="form-control form-control-sm w-50" id="file">
            </td>
          </tr>
        </tbody>
      </table>
      <div class='mt-4 text-center'>
        <button class="btn btn-dark attach-btn me-2"><?=lang('Product.registration')?></button>
        <button class='btn btn-secondary product-csv-btn'>엑셀 다운</button>
      </div>
    </form>
  </div>
  </div>
</main>