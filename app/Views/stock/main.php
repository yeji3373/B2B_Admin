<main>
  <h6>재고관리</h6>
  <?//=view('Auth\Views\_notifications') ?>
  <form method='get' action='<?=base_url('stock')?>'>
    <fieldset class='my-2 px-2 pb-2 border border-secondary show-legend'>
      <legend>검색</legend>
      <div class='d-flex flex-row flex-wrap align-items-end mb-2'>
        <div class='d-flex flex-column'>
          <label class='form-label'>Page Count</label>
          <select class='form-select form-select-sm text-uppercase' name='pageCnt'>
            <?php 
            for( $i = 10; $i <= $pager->getTotal(); $i += 20 ) {
              echo "<option value='$i'";
                if ( $i == $pager->getPerPage() ) {
                  echo 'selected';
                }
              echo ">{$i}</option>";
            }
            ?>
          </select>
        </div>
        <div class='d-flex flex-column ms-2'>
          <label class='form-label'>검색 순서</label>
          <select class='form-select form-select-sm text-uppercase' name='orderBy'>
            <?php 
            $sort = [['val'=> 'stocks.prd_id', 'name'=> '제품등록순']];
            foreach($sort AS $s) {
              echo "<option value='{$s['val']}'>{$s['name']}</option>";
            }
            ?>
          </select>
        </div>
      </div>
      <button type='submit' class='btn btn-primary'>검색</button>
    </fieldset>
  </form>
  <form class='form-edit'>
    <input type='hidden' class='submit-check' value='0'>
    <button type='submit' class='btn btn-sm btn-danger mb-2 edit-btn d-none'>선택 수정</button>
    <table>
      <colgroup>
      </colgroup>
      <thead>
        <tr>
          <th>No</th>
          <th>바코드</th>
          <th>상품명</th>          
          <th>타입</th>
          <!-- <th>체품무게(g)</th> -->
          <th>등록일(입고일)</th>
          <!-- <th>위치</th> -->
          <th>유효기간</th>
          <th>입고수량(낱개)</th>
          <th>출고수량(낱개)</th>
          <th>재고수량(낱개)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($productStocks)) : 
          foreach($productStocks as $i => $stock) : ?>
        <tr>
          <td><?=$i + 1?></td>
          <td>
            <div class='text-start'>
              <p><?=$stock['barcode']?></p>
              <p><?=$stock['productCode']?></p>
            </div>
          </td>
          <td>
            <div class='text-start'>
              <p><?=$stock['name']?></p>
              <p><?=$stock['name_en']?></p>
            </div>
          </td>
          <td>
            <div class='text-start'>
              <p><?=$stock['type']?></p>
              <p><?=$stock['type_en']?></p>
            </div>
          </td>
          <!-- <td>
            <div class='text-start'>
              <p><?//=number_format($stock['shipping_weight'])?></p>
            </div>
          </td> -->
          <td>
            <div class='text-start'>
              <p><?=$stock['supplied_date']?></p>
            </div>
          </td>
          <!-- <td>
            <div class='text-start'>
              <p><?//=$stock['layout_section']?></p>
            </div>
          </td> -->
          <td>
            <div class='text-start'>
              <p><?=$stock['exp_date']?></p>
            </div>
          </td>          
          <td>
            <div class='text-start'>
              <p><?=number_format($stock['supplied_qty'])?></p>
            </div>
          </td>
          <td>
            <div class='text-start'>
              <?=number_format($stock['req_qty']);?>
            </div>
          </td>
          <td>
            <div class='text-start'>
              <?=number_format($stock['supplied_qty'] - $stock['req_qty']);?>
            </div>
          </td>
        </tr>
        <?php endforeach;
        endif; ?>
      </tbody>
    </table>
  </form>
  <?php echo $pager->links(); ?>
</main>