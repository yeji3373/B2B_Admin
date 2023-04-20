<main>
  <div class='border border-dark mb-2 p-2 d-flex flex-row'>
    <a class='btn btn-sm btn-secondary' href='<?=base_url('product/regist')?>'>제품등록/관리</a>
    <!-- <a class='btn btn-sm btn-secondary ms-2' href='<?//=base_url('brand')?>'>브랜드 등록/관리</a> -->
  </div>
  <h6>제품</h6>
  <?= view('Auth\Views\_notifications') ?>

  <form method='get' action='<?=base_url('product')?>'>
    <fieldset class='my-2 px-2 pb-2 border border-secondary show-legend'>
      <legend>검색</legend>
      <div class='d-flex flex-row flex-wrap align-items-end mb-2'>
        <div class='d-flex flex-column'>
          <label class='form-label'>브랜드</label>
          <select class='form-select form-select-sm text-uppercase' name='brand_id'>
            <option value=''>-</option>
            <?php if ( !empty($brands) ) :
            foreach($brands as $brand) : ?>
              <option class='text-uppercase' value='<?=$brand['brand_id']?>' 
              <?=isset($_GET['brand_id']) && $_GET['brand_id'] == $brand['brand_id'] ? 'selected' : '' ?>><?=$brand['brand_name']?></option>
            <?php endforeach;
            endif; ?>
          </select>
        </div>
        <div class='d-flex flex-column ms-1'>
          <label class='form-label'>제품명</label>
          <input class='form-control form-control-sm' type='text' name='name'
            value='<?=isset($_GET['name']) ? $_GET['name'] : ''?>'>
        </div>
        <div class='d-flex flex-column ms-1'>
          <label class='form-label'>바코드</label>
          <input class='form-control form-control-sm' type='text' name='barcode'
            value='<?=isset($_GET['barcode']) ? $_GET['barcode'] : ''?>'>
        </div>
        <div class='d-flex flex-column ms-1'>
          <label class='form-label'>List 개수</label>
          <input class='form-control form-control-sm' type='text' name='pageCnt'
            value='<?=isset($_GET['pageCnt']) ? $_GET['pageCnt'] : ''?>'>
        </div>
      </div>
      <button type='submit' class='btn btn-primary'>검색</button>
    </fieldset>
  </form>

  <form method='post' action='<?=base_url('product/exportData')?>'>
    <fieldset class='my-2 px-2 pb-2 border border-secondary show-legend'>
      <legend>상품정보 Downloads</legend>
      <div class='d-flex flex-row'>
        <input type='hidden' name='prd-include' value='1'>
        <select name='brand_id'
          class='form-select form-select-sm text-uppercase w-25 me-2'>
          <option value>브랜드 선택</option>
          <?php if ( !empty($brands) ) : 
            foreach ($brands as $brand) : ?>
            <option class='text-uppercase' value='<?=$brand['brand_id']?>'><?=$brand['brand_name']?></option>
          <?php endforeach;
            endif ?>
        </select>
        <button type='submit' class='btn btn-sm btn-secondary'>상품 정보 다운</button>
      </div>
    </fieldset>
  </form>

  <form class='form-edit' method='post' action='<?=base_url('product/supplyRate')?>'>
    <input type='hidden' class='submit-check' value='0'>
    <button type='submit' class='btn btn-sm btn-danger mb-2 edit-btn d-none'>선택 수정</button>
    <table>
      <colgroup>
      </colgroup>
      <thead>
        <tr>
          <th colspan='2' rowspan='2'>No</th>
          <th rowspan='2'>Barcode</th>
          <th rowspan='2'>Description</th>
          <!-- <th rowspan='2'>type</th> -->
          <th rowspan='2'>체품무게</th>
          <th rowspan='2'>기타</th>
          <th colspan='2'>가격(원)</th>
          <th rowspan='2'>공급률 적용<br/>(브랜드별)</th>
          <th class='w-8p' rowspan='2'>공급률 적용</th>
          <th rowspan='2'>표시여부</th>
          <th rowspan='2'>판매여부</th>
        </tr>
        <tr>
          <th>소비자가</th>
          <th>공급가</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($products)) : 
          foreach($products as $i => $product) : ?>
        <tr>
          <td>
            <input type='checkbox' class='edit-check value-change' value='0'>
          </td>
          <td class='ids'>
            <input type='hidden' name='product_price[<?=$i?>][idx]' value='<?=$product['id']?>' disabled>
            <input type='hidden' name='product_price[<?=$i?>][brand_id]' value='<?=$product['brand_id']?>' disabled>
            <input type='hidden' name='product_price[<?=$i?>][product_price_idx]' value='<?=$product['product_price_idx']?>' disabled>
            <?=$product['id']?>
          </td>
          <td>
            <div class='d-flex flex-column'>
              <span><?=$product['barcode'] == 0 ? '-' : $product['barcode']?></span>
              <span><?=$product['productCode'] == 0 ? '-' : $product['productCode']?></span>
            </div>
          </td>
          <td class='text-start'>
            <div class='d-flex flex-row'>
              <img src='<?=$status->imageSrc($product['img_url'])?>' style='width: 3rem; height: 3rem;' class='me-1'>
              <a href='<?=base_url("product/edit/{$product['id']}/{$product['brand_id']}")?>'>
                <div>
                  <span class='brand_name bracket'><?=$product['brand_name']?></span>
                  <span><?=$product['name']?></span>
                  <span class='text-capitalize'><?=$product['name_en']?></span>
                </div>
                <div>
                  <span><?=$product['type']?></span>
                  <span><?=$product['type_en']?></span>
                </div>
              </a>
            </div>
          </td>
          <!-- <td class='text-start'>
            <div class='d-flex flex-column'>
              <span><?//=$product['type']?></span>
              <span><?//=$product['type_en']?></span>
            </div>
          </td> -->
          <td>
            <?=number_format($product['shipping_weight'])?>g
          </td>
          <td>
            <?php if ( $product['sample'] == 1 ) { echo "샘플"; } ?>
          </td>
          <td class='text-end'>
            \<?=number_format($product['retail_price'])?>
          </td>
          <td class='text-end'>
            \<?=number_format($product['supply_price'])?>
          </td>
          <td>
            <?php if ( $product['supply_rate_based'] == 1 ) :
              if ( $product['supply_rate_applied'] == 1 ) : 
                echo "제품별 적용";
              else :
                echo number_format(($product['supply_rate_by_brand'] * 100))."%";
              endif;
              else : 
                echo "미적용";
              endif; ?>
          </td>
          <td class='text-start'>
            <div class='d-flex flex-column'>
              <div class="form-check form-switch">
                <label class="form-check-label fs-inherit">
                  <input class="form-check-input value-change"
                    data-idx=<?=$product['product_price_idx']?>
                    data-find-parent='td'
                    data-find-target='[name="product_price[<?=$i?>][supply_rate]"]'
                    data-condition='[{"condition": "0", "action": "disabled", "value": true}
                                  , {"condition": "1", "action": "disabled", "value": false}
                                  , {"condition": "0", "action": "required", "value": false}
                                  , {"condition": "1", "action": "required", "value": true}]'
                    name='product_price[<?=$i?>][supply_rate_applied]'
                    <?=!empty($product['supply_rate_applied']) && $product['supply_rate_applied'] == 1 ? 'checked' : ''?>
                    type="checkbox">
                  공급률적용
                </label>
              </div>
              <div class='d-flex flex-row justify-content-center align-items-end'>
                <input class='form-control form-control-sm me-1' 
                      pattern='[0-9]{1, 3}' 
                      minlength='1'
                      maxlength='3'
                      type='text'
                      name='product_price[<?=$i?>][supply_rate]' 
                      value='<?=!empty($product['supply_rate']) && !is_null($product['supply_rate']) ? ($product['supply_rate'] * 100) : ''?>'
                      <?=!empty($product['supply_rate']) && !is_null($product['supply_rate']) ? '' : 'disabled'?>
                      >%
              </div>
            </div>
          </td>
          <td>
            <?php if ( $product['display'] == 0 ) { echo '<label>표시안함</label>'; } ?>
          </td>
          <td>
            <?php if ( $product['discontinued'] == 1 ) { echo '<label>판매안함</label>'; } ?>
          </td>
        </tr>      
        <?php endforeach;
        endif; ?>
      </tbody>
    </table>
  </form>
  <?php echo $pager->links('default', 'pager'); ?>
</main>