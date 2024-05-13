<?php
namespace Corn\Services;

use Pimple\Container;
use Core\Services\ServiceException;
use Core\Helpers\TextHelper;
use Core\Entities\Email;
use Core\Services\TransactionHelper;

use Configs\GlobalConfig;
use Corn\Mappers\UserMapper;
use Corn\Entities\User;
use Corn\Entities\RegisterUserForm;
use Corn\Entities\SendNewPasswordForm;
use Corn\Entities\UpdateProfileForm;

class UserService
{  
	use Translator;
	private $app;

    private $mapper = NULL;

    private $lockMapper = NULL;

    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->getMapper();
    }

    protected function getMapper()
    {
        if ($this->mapper == NULL) {
            $this->mapper = new UserMapper($this->app['pdo_adapter']);
        }
        return $this->mapper;
    }

   

    /**
     * register a user
     * 
     * @param User $user
     *            return boolean or new user
     */
    public function registerUser(RegisterUserForm $form)
    {
        if ($form == NULL) {
            throw new ServiceException('user is null');
        }
        //find existing email
        $prev = $this->findByEmail($form->email);
        if($prev!=NULL){
			throw new ServiceException('email already registered');
		}
        $user = new User();
        $user->bind($form);
        if (! $user->validate()) {
            throw new ServiceException($user->error_messages());
        }
        
        
        // hash password
        $passhash = password_hash($user->password, PASSWORD_DEFAULT);
        $user->password = $passhash;
        // set created date
        // $user->createdDate = TimeHelper::get_current_time();
        $user->activationCode = TextHelper::generateRandomString();
        // save to db
        $ret = $this->mapper->save($user);
        if ($ret) {
			//send activation email
			$this->sendUserActivationEmail($user);
            return $user;
        }
        return FALSE;
    }
    
	public function activateUser($userID, $activationCode){
		$user = $this->findById($userID);
		if($user==NULL){
			throw new ServiceException('user not found');
		}
		if(trim($user->activationCode)==trim($activationCode)){
			$user->isActive = TRUE;
			return $this->mapper->save($user);
		}
		return FALSE;
	}
    /**
     * admin login using username(email) and password
     * 
     * @param String $username
     * @param String $password
     *            return boolean false or User
     */
    public function login($usernamex, $password)
    {
        $username = trim($usernamex);
        
        TransactionHelper::enableTransaction($this->app);
        
        $user = $this->mapper->findByEmail($username);
        
        if ($user == NULL) {
            return FALSE;
        }
        
        if(!$user->isActive){
			throw new ServiceException('please check your email to activate your user');
		}
        
        if (password_verify($password, $user->password)) {			            
            //$user->lastLoginIPAddress = $this->getClientIPAddress();
            $this->mapper->updateLastLogin($user);
            $publicDir = GlobalConfig::getPublicDir();
			$fpath = $publicDir.'/'.$user->ID.'.png';
            $user->avatarFile = $fpath;
            $user->avatarURL = GlobalConfig::getPublicURL().'/assets/'.$user->ID.'.png';;
            TransactionHelper::commitTransaction($this->app);
            return $user;
        } else {			

            TransactionHelper::commitTransaction($this->app);
            return FALSE;
        }
    }

    public function findById($userId)
    {
        return $this->mapper->findById($userId);
    }

    public function findByEmail($email)
    {
        return $this->mapper->findByEmail($email);
    }
    
	public function findAll()
    {
        return $this->mapper->findAll();
    }

    
    public function updateProfile(UpdateProfileForm $form)
    {
		$user = $this->findById($form->userID);
        if ($user == NULL) {
            throw new ServiceException('user not found');
        }
        // check whether user email already taken
        $prev = $this->mapper->findByEmail($form->email);
        if ($prev != NULL && $prev->ID != $user->ID) {
            throw new ServiceException('email already taken');
        }
        
        $user->email = $form->email;
        $user->firstName = $form->firstName;
        $user->lastName = $form->lastName;
        $user->mobileNo = $form->mobileNo;
        //var_dump($form->imageFile);
        $publicDir = GlobalConfig::getPublicDir();
        $fpath = $publicDir.'/'.$user->ID.'.png';
        //file_put_contents($fpath,$form->imageFile);
        if(isset($_FILES['imageFile'])){
			if ($_FILES["imageFile"]["size"] > 500000) {
			  throw new ServiceException("Sorry, your file is too large. Max 500KB");
			  $uploadOk = 0;
			}
			// Allow certain file formats
			$imageFileType = strtolower(pathinfo($fpath,PATHINFO_EXTENSION));

			if( $imageFileType != "png" ) {
			  throw new ServiceException( "Sorry, only PNG files are allowed.");
			  $uploadOk = 0;
			}
			$namaFile = $_FILES['imageFile']['name'];
			$namaSementara = $_FILES['imageFile']['tmp_name'];

			// pindahkan file
			$terupload = move_uploaded_file($namaSementara, $fpath);

			if ($terupload) {				
				
			} else {
				throw new ServiceException("failed to upload photo");
			}
		}
		

        if (! empty($form->password)) {
            // hash password
            $passhash = password_hash($form->password, PASSWORD_DEFAULT);
            $user->password = $passhash;
        }
        
        // save to db
        $ret = $this->mapper->save($user);
        if ($ret) {
            return $user;
        }
        return FALSE;
    }

    public function sendNewPassword(SendNewPasswordForm $form)
    {
		$newPassword = TextHelper::generateRandomString(7);
        $htmlmsg = sprintf('<p>Your new password is: %s</p>',$newPassword);
        $plainmsg = sprintf('Your new password is: %s', $newPassword);
        
        $user = $this->findByEmail($form->email);
        if($user==NULL){
			throw new ServiceException('Email not found');
		}
		$user->password = $newPassword;
		// hash password
        $passhash = password_hash($user->password, PASSWORD_DEFAULT);
        $user->password = $passhash;
        
        // save to db
        $ret = $this->mapper->save($user);
        if ($ret) {
			$email = new Email();
			$email->fromName = 'Corn Moisture Admin';
			$email->fromEmail = 'aldo@aldoapp.com';
			
			
			$email->subject = 'New Password';
			$email->htmlMessage = $htmlmsg;
			$email->plainTextMessage = $plainmsg;
			$email->toEmail = $user->email;
			$email->toName = $user->firstName. ' '.$user->lastName;
			
			$svc = $this->app['mailerService']();
			
			return $svc->sendEmail($email);
        }
        return FALSE;
			
        
    }

    
    private function sendUserActivationEmail(User $user)
    {
		$path = '/api/user/activate';
		
		$publicURL = GlobalConfig::getPublicURL();
		
        $htmlmsg = sprintf('<p>To activate your account please go this link <a href="%s%s/%d/%s">%s%s/reset/%d/%s</a></p>', $publicURL, $path, $user->ID, $user->activationCode, $publicURL, $path, $user->ID, $user->activationCode);
        $plainmsg = sprintf('To activate your account please go this link <a href="%s%s/%d/%s">%s%s/reset/%d/%s</a>', $publicURL, $path, $user->ID, $user->activationCode, $publicURL, $path, $user->ID, $user->activationCode);
        
        $email = new Email();
        $email->fromName = 'Corn Moisture Admin';
        $email->fromEmail = 'aldo@aldoapp.com';
        
        
        $email->subject = 'User Activation';
        $email->htmlMessage = $htmlmsg;
        $email->plainTextMessage = $plainmsg;
        $email->toEmail = $user->email;
        $email->toName = $user->firstName. ' '.$user->lastName;
        
        $svc = $this->app['mailerService']();
        
        return $svc->sendEmail($email);
    }

}
