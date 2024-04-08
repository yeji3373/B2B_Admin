<?php
namespace Verifyemail\Controllers;

use CodeIgniter\Controller;

/**
 * verifyEmail exception handler
*/
class verifyEmailException extends Exception {
  public function errorMessage() {
    $errorMsg = $this->getMessage();
    return $errorMsg;
  }
}