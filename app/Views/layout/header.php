<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- <link rel="shortcut icon" type="image/png" href="/favicon.ico"/> -->
  <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="/bootstrap/css/bootstrap-utilities.min.css"/>
  <link rel="stylesheet" href="/css/common.css" />
  <link rel="stylesheet" href="/css/layout.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script type="text/javascript" src="/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="/js/common.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <?php if (isset($header) && !empty($header)) : ?>
  <?php foreach ($header as $key=>$val) : 
    if ( $key == 'css') : 
      foreach($val as $css) : 
        if ( strpos( strtolower($css), 'http') !== false ) {
          echo '<link ref="stylesheet" href="'.$css.'">';
        } else echo '<link rel="stylesheet" href="/css'.$css.'">';
      endforeach;
    elseif ( $key == 'js') : 
      foreach ( $val as $js ) : 
        if ( strpos( strtolower($js), 'http') !== false ) {
          echo '<script type="text/javascript" src="'.$js.'"></script>';
        } else echo '<script type="text/javascript" src="/js'.$js.'"></script>';
      endforeach;
    endif;
  endforeach?>
  <?php endif ?>

  <title>B2B ADMIN</title>
</head>
<body>