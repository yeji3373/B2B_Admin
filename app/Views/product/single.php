<div class='single-product-regist-edit-container edit-container'>
  <?php if (isset($product)) : ?>
  <h6>제품수정</h6>
  <?php else: ?>
  <h6><?=lang('Product.singleInput')?></h6>
  <?php endif; ?>
  <form action="<?=site_url('product/singleRegist')?>" method="post" enctype="multipart/form-data">
  <?php if ( !empty($product) ) : ?>
  <input type='hidden' name='product[id]' value='<?=$product['id']?>'>
  <?php endif; ?>
  <table>
    <tbody>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <label><?=lang('Product.brand')?></label>
        </th>
        <td class='text-start'>
          <div class='d-flex flex-row'>
            <?php if ( !isset($brands) ) : ?>
            <input type='hidden' name="product[brand_id]" value='<?=isset($product) ? $product['brand_id'] : ''?>'>
            <span class='brand_name'><?=isset($product) && !empty($product['brand_name']) ? $product['brand_name'] : ''?></span>
            <?php else : ?>
            <select name="product[brand_id]" class='form-select form-select-sm w-50' required>
              <option value><?=lang('Product.brandChoose')?></option>
              <option value='<?=base_url('brand')?>' data-link='1'>브랜드 등록</option>
              <?php foreach ( $brands as $brand ) : ?>
                <option value="<?=$brand['brand_id']?>" 
                  data-supply-applied='<?=$brand['supply_rate_based']?>' 
                  data-supply-rate='<?=($brand['supply_rate_by_brand'] * 100)?>'
                  <?php if ( isset($product) && $product['brand_id'] == $brand['brand_id'] ) { echo 'selected'; } ?>>
                  <?=$brand['brand_name']?>
                </option>
              <?php endforeach ?>
            </select>
            <?php endif; ?>
            <?php if ( !empty($product) && !empty($product['supply_rate_based']) ) : 
                $class = '';
                $applied_supply_rate = ($product['supply_rate_by_brand'] * 100);
                $supply_rate_based = $product['supply_rate_based'];
              else : 
                $class = 'd-none';
                $applied_supply_rate = '';
                $supply_rate_based = '';
            endif; ?>
            <input type='hidden' name='supply_rate_based' value='<?=$supply_rate_based?>'>
            <div class='d-flex flex-row flex-wrap align-items-center ms-4 px-2 text-bg-danger <?=$class?>'>
              <span class='me-1'>공급률</span>
              <span class='applied_rate'>
                <?=$applied_supply_rate?>
              </span>
              <span>% 적용중</span>
            </div>
          </div>
        </td>
      </tr>
      <!-- <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?//=lang('Product.renewal')?>
        </th>
        <td class='text-start'>
          <input type="checkbox" value="1" name="product[renewal">
        </td>
      </tr> -->
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?=lang('Product.barcode')?>
        </th>
        <td class='text-start'>
          <div class='d-grid grid-half'>
            <p class='d-flex flex-column'>
              <label class='mb-1'><?=lang('Product.barcode')?></label>
              <input type="text" name="product[barcode]" placeholder="1234567891234" 
                class='form-control form-control-sm w-100'
                value='<?=(isset($product) && !empty($product['barcode']) ? trim($product['barcode']) : '')?>'>
            </p>
            <p class='d-flex flex-column ms-4'>
              <label class='mb-1'>피디온 Barcode</label>
              <input type='text' name="product[productCode]" placeholder='1234567891234' 
                class='form-control form-control-sm w-100'
                value='<?=(isset($product) && !empty($product['productCode']) ? trim($product['productCode']) : '')?>'>
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?=lang('Product.productName')?>
        </th>
        <td class='text-start'>
          <div class='d-grid grid-half'>
            <p class='d-flex flex-column'>
              <label class='mb-1'><?=lang('Product.productName')?></label>
              <input type="text" name="product[name]" placeholder="상품명"
                class='form-control form-control-sm w-100' 
                value='<?=(isset($product) && !empty($product['name']) ? trim($product['name']) : '')?>'>
            </p>
            <p class='d-flex flex-column ms-4'>
              <label class='mb-1'><?=lang('Product.productNameEng')?></label>
              <input type="text" name="product[name_en]" placeholder="Product Name" 
                class='form-control form-control-sm w-100'
                value='<?=(isset($product) && !empty($product['name_en']) ? trim($product['name_en']) : '')?>'
                required>
            </p>
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?=lang('Product.productType')?>
        </th>
        <td class='text-start'>
          <div class='d-grid grid-half'>
            <p class='d-flex flex-column'>
              <label class='mb-1'><?=lang('Product.productType')?></label>
              <input type="text" name="product[type]" placeholder="21호 라이트베이지"
                class='form-control form-control-sm w-100'
                value='<?=(isset($product) && !empty($product['type']) ? trim($product['type']) : '')?>'>
            </p>
            <p class='d-flex flex-column ms-4'>
              <label class='mb-1'><?=lang('Product.productTypeEng')?></label>
              <input type="text" name="product[type_en]" placeholder="21 pght Beige" 
                class='form-control form-control-sm w-100'
                value='<?=(isset($product) && !empty($product['type_en']) ? trim($product['type_en']) : '')?>'>
            </p>
          </div>
        </td>
      </tr>
      <?php if (isset($product)) : ?>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?=lang('Product.grouping')?>
        </th>
        <td class='text-start'>
          <div class='d-grid grid-half'>
            <div class='d-flex flex-row w-100p'>
              <div class='d-flex w-10p flex-column align-items-center'>
                <label class='mb-1'>그룹핑</label>
                  <input type='checkbox' name='grouping'
                    class='form-check value-change d-flex'
                    <?php if ( isset($product['group_id']) ) { echo 'checked'; } ?>>
              </div>
              <div class='d-flex flex-column w-90p'>
                <label class='mb-1'>그룹 선택</label>
                <select name="group[id]" class='form-select form-select-sm group_choose'>
                  <option value><?=lang('Product.groupChoose')?></option>
                  <option value='new_group'>그룹 등록</option>
                  <?php if(isset($pgroups)) :?>
                    <?php foreach ( $pgroups as $group ) : ?>
                      <option value="<?=$group['id']?>"
                      <?php if ( isset($product) && $product['group_id'] == $group['id'] ) { echo 'selected'; } ?>>
                        <?=$group['group_name']?>
                      </option>
                    <?php endforeach ?>
                  <?php else :?>
                  <?php endif;?>
                </select>
              </div>
            </div>
            <div class='d-flex flex-column ms-4'>
              <label class='mb-1'><?=lang('Product.groupNameEng')?></label>
              <input type="text" name="group[name_new]" placeholder="Group Name" 
                class='form-control form-control-sm w-100 group_name'
                value='<?=(isset($product) && !empty($product['name_en']) ? trim($product['name_en']) : '')?>'
                required>
            </div>
          </div>
        </td>
      </tr>
      <?php endif; ?>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          소비자가
        </th>
        <td class='text-start'>
          <div class='d-flex flex-row'>
            <input type='text' name='product_price[retail_price]' 
              class='form-control form-control-sm w-50'
              value='<?=(isset($supply) && !empty($supply['retail_price'])) ? $supply['retail_price'] : ''?>'>
            
            <label class='d-flex flex-row align-items-center ms-2' style='font-size: 0.8rem; font-weight: initial;'>
              <input type='checkbox' name='product_price[supply_rate_applied]' 
                class='form-check value-change'
                value='<?=!empty($supply) ? $supply['supply_rate_applied'] : ''?>'
                <?=(isset($supply) && ($supply['supply_rate_applied'] == 1)) ? 'checked' : ''?>
                <?=!empty($product) && !empty($product['supply_rate_based']) ? '' : 'disabled'?> >
                &nbsp;상품별 공급률 변경
            </label>
          </div>
        </td>
      </tr>
      <?php if ( isset($supply) && $supply['supply_rate_applied'] == 1 ) {
        $class = '';
      } else {
        $class = 'd-none';
      }
      ?>
      <tr class='supplay_rate_tr <?=$class?>'>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          공급률
        </th>
        <td class='text-start'>
          <div class='d-flex align-items-center'>
            <input type='text' name='product_price[supply_rate]'
              class='form-control form-control-sm w-25'
              placeholder='40'
              <?=(isset($supply) && !empty($supply['supply_rate'])) ? '' : 'disabled'?>
              value='<?=(isset($supply) && !empty($supply['supply_rate'])) ? ($supply['supply_rate'] * 100) : ''?>'>%
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          공급가
        </th>
        <td class='text-start'>
          <div class='d-flex flex-row'>
            <?php if ( isset($supply) ) : ?>
            <input type='hidden' name='product_price[idx]' value='<?=$supply['product_idx']?>'>
            <input type='hidden' name='product_price[product_price_idx]' value='<?=$supply['idx']?>'>
            <input type='hidden' name='product_price[brand_id]' value='<?=$product['brand_id']?>'>
            <?php endif; ?>
            <input type='text' name='product_price[supply_price]' 
              class='form-control form-control-sm w-50'
              data-old='<?=(isset($supply) && !empty($supply['supply_price'])) ? $supply['supply_price'] : '' ?>'
              value='<?=(isset($supply) && !empty($supply['supply_price'])) ? $supply['supply_price'] : '' ?>'
              <?=!empty($supply) && !empty($supply['not_calculating_margin']) ? 'disabled' : ''?>>
            <label class='d-flex flex-row align-items-center ms-2' style='font-size: 0.8rem; font-weight: initial;'>
              <input type='checkbox' name='product_price[not_calculating_margin]'
                    class='form-check value-change'
                    value='<?=!empty($supply) && !empty($supply['not_calculating_margin']) ? $supply['not_calculating_margin'] : ''?>'
                    <?=!empty($supply) && $supply['not_calculating_margin'] == 1 ? 'checked' : ''?>>
                &nbsp;마진 적용 가격 직접 입력
            </label>
          </div> 
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          가격 설정
        </th>
        <td class='text-start'>
          <div class='d-grid grid-quad'>
          <?php if ( !empty($margin) ) : 
            foreach ( $margin AS $i => $m ) : 
            // print_r($m);?>
            <div class='d-flex flex-column
                <?=$i > 0 && $i % 4 > 0 ? 'ms-4': ''?>
                <?=$i >= 4 ? 'mt-2' : ''?>'>
              <label class='mb-1'><?=$m['margin_section']?>구간</label>
              <input type='hidden' name='product_price[price][<?=$i?>][margin_idx]' value='<?=$m['idx']?>'>
              <input type='hidden' name='product_price[price][<?=$i?>][margin_rate]' value=<?=!empty($m['margin_rate']) ? $m['margin_rate'] : ''?>>
              <input type='hidden' name='product_price[price][<?=$i?>][supply_price_idx]' value=<?=!empty($m['supply_price_idx']) ? $m['supply_price_idx'] : ''?>>
              <input type='hidden' name='product_price[price][<?=$i?>][margin_level]' value=<?=$m['margin_level']?>>
              <input type='text' 
                    class='form-control form-control-sm w-100 supply-price-input'
                    name='product_price[price][<?=$i?>][price]'
                    <?php 
                    if ( !empty($m['price'])) : 
                      echo "value=".$m['price'];
                    // else :
                    //   // print_r($supply);
                    //   if ( !empty($m['margin_rate']) ) :
                    //     if ( !empty($supply['price']) ) :
                    //       echo "value=".($supply['price'] * $m['margin_rate']);
                    //     endif;
                    //   endif;
                    else: 
                      echo 'disabled';
                    endif; 
                    ?>
                    <?=!empty($supply) && empty($supply['not_calculating_margin']) ? 'disabled' : ''?>>
            </div>
          <?php endforeach; 
          endif; ?>
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?=lang('Product.isItBox')?>
        </th>
        <td class='text-start'>
          <div class='d-flex flex-column'>
            <div clss='d-flex flex-column flex-wrap'>
              <p class='mb-1'>
                <label style='font-size: 0.8rem; font-weight: initial;'>
                  <input type="radio" name="product[box]"
                    value="0" <?=(isset($product) && $product['box'] == 0 ) ? 'checked': (!isset($product) ? 'checked' : '') ?>> 
                  <?=lang('Product.singleProduct')?>
                </label>
              </p>
              <div class='mb-1 d-flex flex-row align-items-baseline'>
                <p class='mb-1'>
                  <label style='font-size: 0.8rem; font-weight: initial;'>
                    <input type="radio" name="product[box]" value="1" <?=(isset($product) && $product['box'] == 1 ) ? 'checked': '' ?>> 
                    <?=lang('Product.box')?>
                    <!-- <span><lang('Product.boxInMsg', ['10'])></span> -->
                  </label>
                </p>
                <p class='d-flex flex-column align-items-center ms-2 box_pcs'>
                  <input type="text" 
                    placeholder="<?=lang('Product.boxOfPieces')?> ex) 10" 
                    name="product[in_the_box]" 
                    class='form-control form-control-sm'
                    disabled
                    value='<?=(isset($product) && !empty($product['in_the_box']) ? trim($product['in_the_box']) : '')?>'>
                </p>
              </div>
              <div class='mb-1 d-flex flex-row align-items-baseline'>
                <p class='mb-1'>
                  <label style='font-size: 0.8rem; font-weight: initial;'>
                    <input type="radio" name="product[box]" value="2" 
                    <?=(isset($product) && $product['box'] == 2 ) ? 'checked': '' ?>>
                    <?=lang('Product.bundle')?>
                  </label>
                </p>
                <p class='d-flex flex-row box_contents ms-2'>
                  <!-- <label class='mb-1'>묶음 상품(별개의 상품일 경우)</label> -->
                  <input type="text" placeholder="eyebrow/pencil" 
                    name="product[contents_of_box]" 
                    class='form-control form-control-sm'
                    disabled
                    value='<?=(isset($product) && !empty($product['contents_of_box']) ? trim($product['contents_of_box']) : '')?>'>
                </p>
              </div>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?=lang('Product.specSetting')?>
        </th>
        <td class='text-start'>
          <div class='d-grid grid-half'>
            <p class='d-flex flex-column'>
              <label class='mb-1'><?=lang('Product.spec')?></label>
              <input type="text" name="product[spec]" placeholder="30ml" 
                class='form-control form-control-sm w-100'
                value='<?=(isset($product) && !empty($product['spec']) ? trim($product['spec']) : '')?>'>
            </p>
            <p class='d-flex flex-column ms-4'>
              <label class='mb-1'><?=lang('Product.spec')?></label>
              <input type="text" name="product[spec2]" placeholder="1.01 fl.oz."
                class='form-control form-control-sm w-100'
                value='<?=(isset($product) && !empty($product['spec2']) ? trim($product['spec2']) : '')?>'>
            </p>
          </div>
        </td>
      </tr>
      <!-- <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?//=lang('Product.saleChannel')?>
        </th>
        <td class='text-start'>
          <div class='d-flex flex-column'>
            <label style='font-size: 0.8rem; font-weight: initial;'>
              <input type="radio" name="product[sales_channel]" value="0" checked>
              B2B&B2C 판매
            </label>
            <label style='font-size: 0.8rem; font-weight: initial;'>
              <input type="radio" name="product[sales_channel]" value="1">
              <?//=lang('Product.b2b')?>
            </label>
          </div>
        </td>
      </tr> -->
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <label>배송무게(g)</label>
        </th>
        <td class='text-start'>
          <div class='d-flex flex-row align-items-center'>
            <input type="text" name="product[shipping_weight]" placeholder="250"
              class='form-control form-control-sm w-25'
              value='<?=(isset($product) && !empty($product['shipping_weight']) ? trim($product['shipping_weight']) : '')?>'>
              &nbsp;g
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          <?=lang('Product.productImg')?>
        </th>
        <td class='text-start'>
          <div class='d-flex flex-column'>
            <!-- <label class='d-flex flex-row align-items-center me-2' style='font-size: 0.8rem; font-weight: initial;'>
                <input type='checkbox' name='product[img_type_upload]'
                    class='form-check value-change'>
                &nbsp;나중에 등록
              </label> -->
            <div class='d-flex flex-row mb-2'>
              <label class='d-flex flex-row align-items-center me-2 w-20p' style='font-size: 0.8rem; font-weight: initial;'>
                <input type='radio' name='product[img_upload_type]'
                    class='form-check value-change file_upload' 
                    data-find-parent=''
                    data-find-closest='div'
                    data-find-target='[name="product[product_img]"]'
                    data-condition='[ {"condition": "0", "action": "disabled", "value": true}
                                    , {"condition": "1", "action": "disabled", "value": false}]'
                    value="1"
                    checked>
                &nbsp;파일 upload
              </label>
              <input type="file" name="product[product_img]" class='form-control form-control-sm w-50'>
            </div>
            <div class='d-flex flex-row'>
              <label class='d-flex flex-row align-items-center me-2 w-20p' style='font-size: 0.8rem; font-weight: initial;'>
                <input type='radio' name='product[img_upload_type]'
                    class='form-check value-change file_paste'
                    data-find-parent=''
                    data-find-closest='div'
                    data-find-target='[name="product[product_img]"]'
                    data-condition='[ {"condition": "0", "action": "disabled", "value": true}
                                    , {"condition": "1", "action": "disabled", "value": false}]'>
                &nbsp;외부 이미지 등록
              </label>
              <input type="text" name="product[product_img]" class='form-control form-control-sm w-50' disabled>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-bottom border-end w-25'>
          상품 판매 여부
        </th>
        <td class='text-start'>
          <div class='d-flex flex-row'>
            <label style='font-size: 0.8rem; font-weight: initial;'>
              <input type="radio" name="product[discontinued]" value="0" 
                <?=(isset($product) && $product['discontinued'] == 0 ) ? 'checked': (!isset($product) ? 'checked' : '') ?>>
              판매
            </label>
            <label class='ms-4' style='font-size: 0.8rem; font-weight: initial;'>
              <input type="radio" name="product[discontinued]" value="1"
                <?=(isset($product) && $product['discontinued'] == 1 ) ? 'checked': '' ?>>
              단종
            </label>
          </div>
        </td>
      </tr>
      <tr>
        <th class='border border-0 border-dark border-end w-25'>
          상품 표시
        </th>
        <td class='text-start'>
          <div class='d-flex flex-row'>
            <label style='font-size: 0.8rem; font-weight: initial;'>
              <input type="radio" name="product[display]" value="0" 
                <?=(isset($product) && $product['display'] == 0 ) ? 'checked': (!isset($product) ? 'checked' : '') ?>>
              미표시
            </label>
            <label class='ms-4' style='font-size: 0.8rem; font-weight: initial;'>
              <input type="radio" name="product[display]" value="1"
                <?=(isset($product) && $product['display'] == 1 ) ? 'checked': '' ?>>
              표시
            </label>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
  <div class='text-end mt-4'>
    <button type="submit" class="btn btn-dark" ><?=lang('Product.registration')?></button>
  </div>
  </form>
</div>