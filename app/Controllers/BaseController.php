<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['url', 'form', 'html'];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    public function basicLayout(String $page, Array $data = [], Array $options = [] )
    {
      if ( !is_file(APPPATH.'/Views/'.$page.'.php'))
      {
        return new \CodeIgniter\Exceptions\PageNotFoundException($page);
      }

      $header = []; $footer = [];

      if ( isset($data['header']) && !empty($data['header']) ) $header = $data['header']; unset($data['header']);
      if ( isset($data['footer']) && !empty($data['footer']) ) $footer = $data['footer']; unset($data['footer']);

      echo view('layout/header', $header).
          view($page, $data, $options).
          view('layout/footer', $footer);
    }

    public function menuLayout(String $page, Array $data = [], Array $options = [] )
    {
      if ( !is_file(APPPATH.'/Views/'.$page.'.php'))
      {
        return new \CodeIgniter\Exceptions\PageNotFoundException($page);
      }

      $header = []; 
      $footer = [];

      if ( isset($data['header']) && !empty($data['header']) ) $header = $data['header']; unset($data['header']);
      if ( isset($data['footer']) && !empty($data['footer']) ) $footer = $data['footer']; unset($data['footer']);

      echo view('layout/header', ['header' => $header]).
          view('layout/menu', $data, $options).
          view('Auth\Views\_notifications').
          view($page, $data, $options).
          view('layout/footer', ['footer' => $footer]);
    }

}
