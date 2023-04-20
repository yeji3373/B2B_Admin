<?php
namespace Auth\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class Auth implements FilterInterface {
  public function __construct() {
    $this->session = Services::session();
    // $this->config = config('Auth'); // 필요없음
  }

  public function before(RequestInterface $request, $arguments = null) {
    if ( !$this->session->isLoggedIn ) {
      return redirect()->to('login');
    }
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {
    // Do something here
  }
}