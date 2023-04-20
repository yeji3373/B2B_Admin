<?php
namespace App\Filtes;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Paypal implements FilterInterface {
  public function before(RequestInterface $requset, $arguments = null) {
    if ( !session()->isLoggedIn ) {
      return redirect()->to(site_url('auth'));
    }
  }
}