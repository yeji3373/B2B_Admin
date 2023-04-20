<div class='single-product-regist-edit-container'>
  <p><?=lang('Product.singleInput')?></p>
  <form action="<?=site_url('product/singleRegist')?>" method="post" enctype="multipart/form-data">
    <p>
      <select name="brand_id">
        <option value selected><?=lang('Product.brandChoose')?></option>
        <?php foreach ( $brands as $brand ) : ?>
          <option value="<?=$brand['brand_id']?>"><?=$brand['brand_name']?></option>
        <?php endforeach ?>
      </select>
      <label><?=lang('Product.brand')?></label>
    </p>
    <p>
      <input type="file" name="product_img" id="product_img">
      <label for="product_img"><?=lang('Product.productImg')?></label>
    </p>
    <p>
      <label class='radio-group'>
        <input type="checkbox" value="1" name="renewal">
        <?=lang('Product.renewal')?>
      </label>
    </p>
    <div style='display: flex; flex-direction: row;'>
      <p style='width: 48%; margin-left:0;'>
        <input type="text" name="barcode" placeholder="1234567891234">
        <label><?=lang('Product.barcode')?></label>
      </p>
      <p style='width: 48%; margin-left: 0;'>
        <input type='text' name='productCode' placeholder='1234567891234'>
        <label>피디온 Barcode</label>
      </p>
    </div>
    <div style='display: flex; flex-direction: row;'>
      <p style='width: 48%; margin-left: 0;'>
        <input type="text" name="name" placeholder="상품명">
        <label><?=lang('Product.productName')?></label>
      </p>
      <p style='width: 48%; margin-left: 0;'>
        <input type="text" name="name_en" placeholder="Product Name">
        <label><?=lang('Product.productNameEng')?></label>
      </p>
    </div>
    <div style='display: flex; flex-direction: row;'>
      <p style='width: 48%; margin-left: 0;'>
        <input type="text" name="type" placeholder="21호 라이트베이지">
        <label><?=lang('Product.productType')?></label>
      </p>
      <p style='width: 48%; margin-left: 0;'>
        <input type="text" name="type_en" placeholder="21 pght Beige">
        <label><?=lang('Product.productTypeEng')?></label>
      </p>
    </div>
    <fieldset class="_group">
      <legend><?=lang('Product.isItBox')?></legend>
      <label>
        <input type="radio" name="box" value="0" checked> 
        <?=lang('Product.singleProduct')?>
      </label>
      <label>
        <input type="radio" name="box" value="1"> 
        <?=lang('Product.box')?>
        <!-- <span><lang('Product.boxInMsg', ['10'])></span> -->
      </label>
      <label>
        <input type="radio" name="box" value="2"> 
        <?=lang('Product.bundle')?>
      </label>
      <p class='box_pcs'>
        <input type="text" placeholder="10" name="in_the_box">
        <label><?=lang('Product.boxOfPieces')?></label>
      </p>
      <p class='box_contents'>
        <input type="text" placeholder="eyebrow/pencil" name="contents_of_box">
        <label>묶음 상품(별개의 상품일 경우)</label>
      </p>
    </fieldset>
    <fieldset>
      <legend><?=lang('Product.specSetting')?></legend>
      <div style='display: flex; flex-direction: row;'>
        <p>
          <input type="text" name="spec" placeholder="30ml">
          <label><?=lang('Product.spec')?></label>
        </p>
        <p>
          <input type="text" name="spec2" placeholder="1.01 fl.oz.">
          <label><?=lang('Product.spec')?></label>
        </p>
      </div>
      <p>
        <label>
          <input type="checkbox" name="container">
          <?=lang('Product.specContiner')?>
        </label>
      </p>
      <fieldset style='display: flex; flex-direction: row;'>
        <legend><?=lang('Product.specDetail')?></legend>
        <p style='width: 48%; margin: 0;'>
          <input type="text" name="spec_detail" placeholder="1.5g">
        </p>
        <p style='width: 48%; margin: 0; margin-left: inherit;'>
          <input type="text" name="spec_pcs" placeholder="60">
          <!-- <label><?=lang('Product.specDetail')?></label> -->
        </p>
      </fieldset>
    </fieldset>
    <fieldset>
      <legend><?=lang('Product.saleChannel')?></legend>
      <label>
        <input type="radio" name="sales_channel" value="0" checked>B2B&B2C 판매
      </label>
      <label>
        <input type="radio" name="sales_channel" value="1"><?=lang('Product.b2b')?>
      </label>
    </fieldset>
    <!-- <p>
      <input type="text" name="unit_weight" placeholder="20">
      <label>상품무게(g)</label>
    </p> -->
    <p>
      <input type="text" name="shipping_weight" placeholder="25">
      <label>배송무게(g)</label>
    </p>
    <fieldset>
      <legend>상품 판매 여부</legend>
      <!-- <p class='notice'>상품의 상태값</p> -->
      <label>
        <input type="radio" name="discontinued" value="0" checked>판매
      </label>
      <label>
        <input type="radio" name="discontinued" value="1">단종
      </label>
    </fieldset>
    <fieldset>
      <legend>표시상태</legend>
      <label>
        <input type="radio" name="display" value="0" checked>미표시
      </label>
      <label>
        <input type="radio" name="display" value="1">표시
      </label>
    </fieldset>
    <input type="submit" name="submit" value="<?=lang('Product.registration')?>" class="btn btn-dark" />
  </form>
</div>