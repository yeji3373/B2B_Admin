<main>
  <div class='border border-dark mb-2 p-2'>
    <a class='btn btn-sm btn-secondary' href='<?=base_url('product/regist')?>'>제품등록</a>
  </div>
  <h6>제품</h6>
  <?php if ( session()->has('error') ) : ?>
    <div class='notification error mb-2'>
      <?=session('error');?>
    </div>
  <?php endif; ?>
  <form method='get' action='<?=base_url('product')?>'>
    <fieldset class='my-2 px-2 pb-2 border border-secondary show-legend'>
      <legend>검색</legend>
      <div class='d-flex flex-row flex-wrap align-items-end mb-2'>
        <div class='d-flex flex-column'>
          <label class='form-label'>브랜드</label>
          <select class='form-select form-select-sm' name='brand_id'>
            <option value=''>-</option>
            <?php if ( !empty($brands) ) :
            foreach($brands as $brand) : ?>
              <option value='<?=$brand['brand_id']?>' 
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
          <label class='form-label'>표시여부</label>
          <div class='d-flex flew-row'>
            <div class='form-check form-switch'>
              <label class='form-check-label'>
                <input class='form-check-input' type='checkbox' 
                  name='display[]' 
                  value='1'
                  <?php if ( isset($_GET['display']) ) :
                    if ( $_GET['display'] == 1 ) :
                      echo "checked";
                    endif;
                  else : 
                    echo 'checked';
                  endif; ?>>
                표시함
              </label>
            </div>
            <div class='form-check form-switch ms-2'>
              <label class='form-check-label'>
                <input class='form-check-input' type='checkbox'
                  name='display[]'
                  value='0'
                  <?php if ( isset($_GET['display']) ) :
                    if ( $_GET['display'] == 0 ) :
                      echo "checked";
                    endif;
                  else : 
                    echo 'checked';
                  endif; ?>>
                표시안함
              </label>
            </div>
          </div>
        </div>
      </div>
      <button type='submit' class='btn btn-primary'>검색</button>
    </fieldset>
  </form>
  <table>
    <colgroup>
    </colgroup>
    <thead>
      <th>No</th>
      <th>Barcode</th>
      <th>Description</th>
      <th>체품무게</th>
      <th>type</th>
      <th>기타</th>
      <th>표시여부</th>
      <th>판매여부</th>
    </thead>
    <tbody>
      <?php if (!empty($products)) : 
        foreach($products as $i => $product) : ?>
      <tr>
        <td><?=$product['id']?></td>
        <td>
          <div class='d-flex flex-column'>
            <span><?=$product['barcode'] == 0 ? '-' : $product['barcode']?></span>
            <span><?=$product['productCode'] == 0 ? '-' : $product['productCode']?></span>
          </div>
        </td>
        <td class='text-start'>
          <div class='d-flex flex-row'>
            <img src='<?=$status->imageSrc($product['img_url'])?>' style='width: 3rem; height: 3rem;' class='me-1'>
            <a href='<?=base_url("product/edit/{$product['id']}")?>'>
              <div>
                <span class='brand_name bracket'><?=$product['brand_name']?></span>
                <span><?=$product['name']?></span>
                <span><?=$product['name_en']?></span>
              </div>
            </a>
          </div>
        </td>
        <td>
          <?=number_format($product['shipping_weight'])?>g
        </td>
        <td class='text-start'>
          <div class='d-flex flex-column'>
            <span><?=$product['type']?></span>
            <span><?=$product['type_en']?></span>
          </div>
        </td>
        <td>
          <?php if ( $product['sample'] == 1 ) { echo "샘플"; } ?>
        </td>
        <td>
          <?php if ( $product['display'] == 0 ) { echo '표시안함'; } ?>
        </td>
        <td>
          <?php if ( $product['discontinued'] == 1 ) { echo '판매안함'; } ?>
        </td>
      </tr>      
      <?php endforeach;
      endif; ?>
    </tbody>
  </table>
  <?php echo $pager->links('default', 'page'); ?>
</main>