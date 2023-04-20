<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main') ?>

<h1><?= lang('Auth.login') ?></h1>

<?= view('Auth\Views\_notifications') ?>

<form method="POST" class='inputLabel' action="<?= site_url('login'); ?>" accept-charset="UTF-8">
  <p>
    <label><?= lang('Auth.id') ?></label><br />
    <input required minlength="2" type="text" name="id" value="<?= old('id') ?>" />
  </p>
  <p>
    <label><?= lang('Auth.password') ?></label><br />
    <input required minlength="5" type="password" name="password" value="" />
  </p>
  <p>
    <?= csrf_field() ?>
    <button type="submit"><?= lang('Auth.login') ?></button>
  </p>
  <p>
    <a href="<?= site_url('forgot-password'); ?>" class="float-right"><?= lang('Auth.forgotYourPassword') ?></a>
    <a href="<?= site_url('register'); ?>" class="float-right"><?= lang('Auth.register') ?></a>
  </p>
</form>

<?= $this->endSection() ?>