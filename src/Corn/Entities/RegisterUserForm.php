<?php
namespace Corn\Entities;
use Core\Entities\BaseEntity;

class RegisterUserForm extends BaseEntity{
	public $firstName;
	public $lastName;
	public $email;
	public $password;
	public $mobileNo;
	
	public function validate(){
		$this->required(['firstName', 'lastName','mobileNo', 'email', 'password']);
		if(!empty($this->email)){
			//validate email
			if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
				// invalid emailaddress
				$this->add_error('email', 'Invalid Email');
			}
		}
		return !$this->has_error();
	}
}
