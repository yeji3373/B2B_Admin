<?php

$routes->group('paypal', ['namespace' => 'Paypal\Controllers'], function($routes) {
  // $routes->get('', 'PaypalController::index');
  // $routes->post('', 'PaypalController::index');
  // $routes->get('index', 'PaypalController::index');

  // invoice Detail
  $routes->get('detail/(:any)', 'PaypalController::showInvoiceDetail/$1', ['as' => 'detail']);
  // $routes->post('detail', 'PaypalController::showInvoiceDetail');
});