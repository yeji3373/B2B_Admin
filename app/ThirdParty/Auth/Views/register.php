<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main') ?>

<h1><?= lang('Auth.registration') ?></h1>
<?= view('Auth\Views\_notifications') ?>

<!-- <form method="POST" action="<?= route_to('register'); ?>" accept-charset="UTF-8" onsubmit="registerButton.disabled = true; return true;"> -->
<form method="POST" class='inputLabel' action="<?= route_to('register'); ?>" accept-charset="UTF-8" onsubmit="registerCheck();">
	<?= csrf_field() ?>
	<p>
    <input required minlength="2" type="text" name="name" value="<?= old('name') ?>"  placeholder=" " />
    <label><?= lang('Auth.name') ?></label>
	</p>
  <p>
    <input required minlength="2" maxlength="" type="text" name="id" value="<?= old('id') ?>" placeholder=" " />
    <label><?= lang('Auth.id') ?></label>
	</p>
	<p>
    <input required type="email" name="email" value="<?= old('email') ?>" placeholder=" " />
    <label><?= lang('Auth.email') ?></label>
	</p>
	<p>
    <input required minlength="5" type="password" name="password" value="" placeholder=" " />
    <label><?= lang('Auth.password') ?></label>
	</p>
	<p>
    <input required minlength="5" type="password" name="password_confirm" value="" placeholder=" " />
    <label><?= lang('Auth.passwordAgain') ?></label>
	</p>
	<p>
    <button name="registerButton" type="submit"><?= lang('Auth.register') ?></button>
	</p>
	<p>
		<a href="<?= site_url('login'); ?>" class="float-right"><?= lang('Auth.alreadyRegistered') ?></a>
	</p>
</form>
<?= $this->endSection() ?>