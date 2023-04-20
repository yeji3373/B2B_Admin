<?php 
namespace Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table      = 'manager';
	protected $primaryKey = 'idx';

	protected $returnType = 'array';
	protected $useSoftDeletes = false;

	// // this happens first, model removes all other fields from input data
	// protected $allowedFields = [
	// 	'name', 'email', 'new_email', 'password', 'password_confirm',
	// 	'activate_hash', 'reset_hash', 'reset_expires', 'active'
	// ];
  protected $allowedFields = [
		'role_id', 'id', 'name', 'email', 'password', 'contact_us', 
    'region_id', 'active'
	];

	protected $useTimestamps = true;
	protected $createdField  = 'created_at';
	protected $updatedField  = 'updated_at';
	protected $dateFormat  	 = 'datetime';

	protected $validationRules = [];

	// we need different rules for registration, account update, etc
	protected $dynamicRules = [
		'registration' => [
			'name' 				=> 'required|min_length[2]',
			'email' 			=> 'required|valid_email|is_unique[users.email]',
			'password'			=> 'required|min_length[4]',
			'password_confirm'	=> 'matches[password]'
		],
		'updateAccount' => [
			'id'	=> 'required|is_natural_no_zero',
			'name'	=> 'required|min_length[2]'
		],
		'changeEmail' => [
			'id'			=> 'required|is_natural_no_zero',
			'email'		=> 'required|valid_email|is_unique[users.email]',
			// 'activate_hash'	=> 'required'
		]
	];

	protected $validationMessages = [];

	protected $skipValidation = false;

	// this runs after field validation
	protected $beforeInsert = ['hashPassword'];
	protected $beforeUpdate = ['hashPassword'];


  //--------------------------------------------------------------------

  /**
   * Retrieves validation rule
   */
	public function getRule(string $rule) {
		return $this->dynamicRules[$rule];
	}

  //--------------------------------------------------------------------

  /**
   * Hashes the password after field validation and before insert/update
   */
	protected function hashPassword(array $data)
	{
		if (! isset($data['data']['password'])) return $data;

    $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
		unset($data['data']['password_confirm']);

		return $data;
	}

}