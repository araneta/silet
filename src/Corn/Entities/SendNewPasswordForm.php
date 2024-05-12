<?php
namespace Corn\Entities;
use Core\Entities\BaseEntity;

class SendNewPasswordForm extends BaseEntity{
	public $email;
	
	public function validate(){
		$this->required(['email']);
		if(!empty($this->email)){
			//validate email
			if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
				// invalid emailaddress
				$this->add_error('username', 'Invalid Email');
			}
		}
		return !$this->has_error();
	}
}
