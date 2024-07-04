<main>
	<form method='GET' action='<?=base_url('cafe24/ipList')?>'>
		<input type='hidden' name='page' value='1'>
		<fieldset class='w-55p my-2 px-2 pb-2 border border-dark border-secondaty d-flex flex-row justify-content-between align-items-end'>
			<div class='d-flex flex-row'>
				<div class='form-group me-2'>
					<label>국가</label>
					<select class='form-select' name='ip_nation'>
						<option value=''>전체</option>
						<?php if(!empty($nation)) : 
							foreach($nation AS $i => $n) : ?>
							<option value='<?=$n['ip_nation']?>' <?=(isset($_GET['ip_nation']) && $_GET['ip_nation'] == $n['ip_nation']) ? 'selected' : ''?>><?=$n['name_en']?></option>
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
			<input type='submit' class='btn btn-primary search-btn' value='search'>
		</fieldset>
	</form>
	<div class='d-flex flex-row justify-content-between m-2'>
		<p class='mb-0'>Cafe24 IP LIST</p>
		<button type='button' data-bs-toggle='modal' data-bs-target='#ipRegisterModal' class='btn btn-outline-primary'>IP 등록</button>
		<!-- <title>Cafe24 IP LIST</title> -->
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
					<th>No</th>
					<th>ip</th>
					<th>nation</th>
					<th>corperate name</th>
					<th>업데이트일자</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($list as $i => $ip) : ?>
				<tr>
					<td><?=$i + 1?></td>
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
	<div class='modal' id='ipRegisterModal' tabindex='-1' aria-hidden='true'>
		<div class='modal-dialog modal-dialog-centered'>
			<form method='GET' action='<?=base_url('cafe24/ipRegister')?>' id='ipModalForm' class='mx-auto'>
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
									<input class='form-control' type='text' name='modal_ip_nation' value='' disabled>
								</div>
								<div class='form-group'>
									<label>corperate name</label>
									<input class='form-control' type='text' name='modal_corp_name' value='<?=isset($_GET['corp_name']) ? $_GET['corp_name'] : ''?>'>
								</div>
							</div>
						</fieldset>
					</div>
					<div class='modal-footer'>
						<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
						<input type='button' class='btn btn-primary save-btn' value='Save changes'>
					</div>
				</div>
			</form>
		</div>
	</div>
</main>