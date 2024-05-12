<?php
namespace Corn\Entities;
use Core\Entities\BaseEntity;

class UpdateProfileForm extends BaseEntity{
	
	public $email;
	public $firstName;
	public $lastName;
	public $password;
	public $userID;
	public $mobileNo;
	public $imageFile;
	
	public function validate(){
		$this->required(['userID','firstName', 'lastName','mobileNo', 'email', 'password']);
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
