<?php
namespace Auth\Controllers;

use CodeIgniter\Controller;
use Config\Email;
use Config\Services;
use Auth\Models\UserModel;

class LoginController extends Controller
{
	/**
	 * Access to current session.
	 *
	 * @var \CodeIgniter\Session\Session
	 */
	protected $session;

	/**
	 * Authentication settings.
	 */
	protected $config;


    //--------------------------------------------------------------------

	public function __construct()
	{
		// start session
		$this->session = Services::session();

		// load auth settings
		$this->config = config('Auth');
	}

    //--------------------------------------------------------------------

	/**
	 * Displays login form or redirects if user is already logged in.
	 */
	public function login()
	{
		if ($this->session->isLoggedIn) {
			// return redirect()->to('account');
      return redirect()->to('/');
		}

		return view($this->config->views['login'], ['config' => $this->config]);
	}

    //--------------------------------------------------------------------

	/**
	 * Attempts to verify user's credentials through POST request.
	 */
	public function attemptLogin()
	{
		// validate request
		$rules = [
			'id' 		    => 'required|min_length[2]',
			'password' 	=> 'required|min_length[5]',
		];

		if (! $this->validate($rules)) {
			return redirect()->to('login')->withInput()->with('errors', $this->validator->getErrors());
		}

		// check credentials
		$users = new UserModel();
		$user = $users->where('id', $this->request->getPost('id'))->first();

		if ( is_null($user) || ! password_verify($this->request->getPost('password'), $user['password']) ) {
			return redirect()->to('login')->withInput()->with('error', lang('Auth.wrongCredentials'));
		}

		// check activation
		if (!$user['active']) {
			return redirect()->to('login')->withInput()->with('error', lang('Auth.notActivated'));
		}

    if ($user['role_id'] > 2 ) {
      if ( $user['role_id'] == 1 ) {
        // 최고 상단 권한. 권한 그룹 관리 관련 수정 필요
      }
      // 접근권한없음.
      return redirect()->to('login')->withInput()->with('error', lang('Auth.wrongCredentials'));
    }

		// login OK, save user data to session
		$this->session->set('isLoggedIn', true);
		$this->session->set('userData', [
		    'id' 			    => $user['id'],
        'idx'         => $user['idx'],
        'department'  => $user['department_id'],
		    'name' 			  => $user['name'],
		    'email' 		  => $user['email'],
		]);

    // return redirect()->to('account');
    return redirect()->to('/');
	}
  //--------------------------------------------------------------------

	/**
	 * Log the user out.
	 */
	public function logout()
	{
		$this->session->remove(['isLoggedIn', 'userData']);

		return redirect()->to('login');
	}

}