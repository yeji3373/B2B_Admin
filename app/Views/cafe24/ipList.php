<main>
	<div class='d-flex flex-row w-100p'>
		<div class='w-50p'>
			<form method='GET' action='<?=base_url('cafe24/ipList')?>' class='w-100p'>
				<input type='hidden' name='page' value='1'>
				<fieldset class='w-95p my-2 px-2 pb-2 border border-dark border-secondaty d-flex flex-row justify-content-between align-items-end'>
					<div class='d-flex flex-row'>
						<div class='form-group me-2'>
							<label>국가</label>
							<select class='form-select' name='ip_nation' style='min-width: 13rem;'>
								<option value=''>전체</option>
								<?php 
                if(!empty($nation)) :
									foreach($nation AS $i => $n) : ?>
                  <option value='<?=$n['ip_set_idx']?>'
                    <?=isset($_GET['ip_nation']) && $_GET['ip_nation'] == $n['ip_set_idx'] ? 'selected' : '' ?>>
                    <?=$n['name']?>
                  </option>
									<?php endforeach;
								endif;?>
								<?php ?>
							</select>
						</div>
						<div class='form-group me-2'>
							<label>IP</label>
							<input class='form-control w-100' type='text' name='ip' value='<?=isset($_GET['ip']) ? $_GET['ip'] : ''?>'>
						</div>
						<div class='form-group'>
							<label>회사명</label>
							<input class='form-control w-100' type='text' name='corp_name' value='<?=isset($_GET['corp_name']) ? $_GET['corp_name'] : ''?>'>
						</div>
					</div>
					<input type='submit' class='btn btn-primary search-btn' value='SEARCH'>
				</fieldset>
			</form>
		</div>
		<div class='w-50p'>
			<div class='d-flex flex-row border border-dark my-2 px-2 pb-2'>
				<div class='me-3'>
					<label>beautynetkorea IP</label>
					<input class='form-control' type='text' value='<?=!empty($bnkIP['ip']) ? $bnkIP['ip'] : ''?>' disabled>
				</div>
				<div class='d-flex align-items-end'>
					<button type='button' data-bs-toggle='modal' data-bs-target='#bnkIpUpdateModal' class='btn btn-primary'>UPDATE</button>
				</div>
			</div>
		</div>
	</div>
	<div class='d-flex flex-row justify-content-between m-2'>
		<div>
			<p class='fs-3 mb-0'>Cafe24 IP LIST</p>
		</div>
		<div>
      <button type='button' data-bs-toggle='modal' data-bs-target='#registerModal' data-bs-whatever='country' class='btn btn-outline-success'>차단국가 등록</button>
			<button type='button' data-bs-toggle='modal' data-bs-target='#ipRegisterModal' class='btn btn-outline-primary'>IP 등록</button>
			<button type='button' id='ipDelete' class='btn btn-outline-danger'>삭제</button>
		</div>
	</div>
	<?php if(!empty($list)) : ?>
	<div>
		<table>
			<colgroup>
				<col style='width: 3%'>
				<col style='width: 27%'>
				<col style='width: 20%'>
				<col style='width: 30%'>
				<col style='width: 20%'>
			</colgroup>
			<thead>
				<tr>
					<th><input type='checkbox' class='select-all' value='0'></th>
					<th>ip</th>
					<th>nation</th>
					<th>corperate name</th>
					<th>업데이트일자</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($list as $i => $ip) : ?>
				<tr>
					<td>
						<input type='checkbox' class='value-change' value='0'>
						<input type='hidden' value='<?=$ip['idx']?>'>
					</td>
					<td><?= $ip['ip'] ?></td>
					<td><?= !empty($ip['name_en']) ? $ip['name_en'] : '' ?></td>
					<td><?= !empty($ip['corp_name']) ? $ip['corp_name'] : '' ?></td>
					<td><?= $ip['updated_at'] ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php else : ?>
		<div>is empty!</div>
	<?php endif ?>
	<?=$pager->links('default', 'pager')?>
  <div class='modal' id='registerModal' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered'>
			<form method='POST' action='<?=base_url('cafe24/ipRegister')?>' id='ipModalForm' class='ip-modal-form mx-auto'>
				<div class='modal-content'>
					<div class='modal-header'>
						<h5 class="modal-title" id="exampleModalLabel">차단국가 등록</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class='modal-body'>
						<fieldset class='my-2 px-2 pb-2 d-flex'>
							<div class='d-flex flex-column'>
								<div class='form-group'>
									<label>국가</label>
                  <input type='hidden' name='country_reg' value=1>
									<select class='form-control' type='text' name='country_id'>
                    <?php if ( !empty($countries) ) : 
                      foreach( $countries AS $country ) : ?>
                      <option value='<?=$country['id']?>'><?=$country['name']?></option>
                    <?php endforeach; 
                    endif; ?>
                  </select>
								</div>
							</div>
						</fieldset>
					</div>
					<div class='modal-footer'>
						<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
						<button type='button' class='btn btn-primary save-btn'>Save changes</button>
					</div>
				</div>
			</form>
		</div>
  </div>
	<div class='modal' id='ipRegisterModal' tabindex='-1' aria-hidden='true'>
		<div class='modal-dialog modal-dialog-centered'>
			<form method='POST' action='<?=base_url('cafe24/ipRegister')?>' id='ipModalForm' class='ip-modal-form mx-auto'>
				<div class='modal-content'>
					<div class='modal-header'>
						<h5 class="modal-title" id="exampleModalLabel">IP 등록</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class='modal-body'>
						<fieldset class='my-2 px-2 pb-2 d-flex'>
							<div class='d-flex flex-column'>
								<div class='form-group'>
									<label>IP</label>
									<div class='d-flex flex-row'>
										<input class='form-control w-90p me-1 ip' type='text' name='modal_ip' value=''>
										<button type='button' class='btn btn-secondary check-btn'>check</button>
									</div>
								</div>
								<div class='form-group'>
									<label>국가</label>
									<input class='form-control' type='hidden' name='modal_ip_nation' value=''>
									<input class='form-control' type='text' name='modal_ip_nation_name' value='' readonly>
								</div>
								<div class='form-group'>
									<label>회사명</label>
									<input class='form-control' type='text' name='modal_corp_name' value=''>
								</div>
							</div>
						</fieldset>
					</div>
					<div class='modal-footer'>
						<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
						<button type='button' class='btn btn-primary save-btn'>Save changes</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class='modal' id='bnkIpUpdateModal' tabindex='-1' aria-hidden='true'>
		<div class='modal-dialog modal-dialog-centered'>
			<form method='POST' action='<?=base_url('cafe24/bnkIpUpdate')?>' id='bnkIpModalForm' class='bnk-ip-modal-form mx-auto'>
				<div class='modal-content'>
					<div class='modal-header'>
						<h5 class="modal-title">beautynetkorea IP</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class='modal-body'>
						<fieldset class='my-2 px-2 pb-2 d-flex'>
							<div class='d-flex flex-column'>
								<div class='form-group'>
									<label>기존 자사 IP</label>
									<input class='form-control' type='text' value='<?=!empty($bnkIP['ip']) ? $bnkIP['ip'] : ''?>' disabled>
								</div>
								<div class='form-group'>
									<label>신규 자사 IP</label>
										<input class='form-control' type='text' name='modal_bnk_ip' value=''>
										<input type='hidden' name='modal_bnk_yn' value='1'>
								</div>
							</div>
						</fieldset>
					</div>
					<div class='modal-footer'>
						<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
						<button type='button' class='btn btn-primary save-btn'>Update</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</main>