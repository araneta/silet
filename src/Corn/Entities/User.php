<?php
namespace Corn\Entities;

use Core\Entities\BaseEntity;

class User extends BaseEntity{
	
	public $ID = NULL;
	public $firstName;//
	public $lastName;//
	public $email;
	public $mobileNo;
	public $password;
	
	public $isActive;
	public $activationCode;
	
	public $forgottenPasswordCode;
	public $forgottenPasswordTime;
	public $lastAccess;
	public $createdAt;
	public $updatedAt;
	public $deletedAt;
	
	
	
    
    public function validate(){
        $requiredFields = ['firstName', 'lastName', 'email','mobileNo','password'];
        $this->required($requiredFields);
        if(!empty($this->email)){
			$this->email = preg_replace('/\s+/', '', $this->email);

            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                // invalid emailaddress
                $this->add_error('email', 'Invalid Email: '.$this->email );
            }
        }else{
            $this->add_error('email', 'Enter Email');
        }
        return !$this->has_error();
    }
    
}
