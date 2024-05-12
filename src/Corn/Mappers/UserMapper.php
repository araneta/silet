<?php

namespace Corn\Mappers;

use Core\Helpers\TimeHelper;
use Core\Mappers\AbstractDataMapper;
use Core\Mappers\DatabaseAdapterInterface;
use Corn\Entities\User;


class UserMapper extends AbstractDataMapper
{

    protected $entityTable = '"User"';

    public function __construct(DatabaseAdapterInterface $adapter)
    {
        parent::__construct($adapter);
    }

    /**
     * abstract method
     */
    protected function createEntity(array $row)
    {
        $entity = new User();
        $entity->bind($row);
        return $entity;
    }

    
    /**
     * save user to db
     * @param User $user
     * return boolean
     */
    public function save(User &$user)
    {
        $data = [
            '"firstName"' => $user->firstName,
            '"lastName"' => $user->lastName,
            '"mobileNo"' => $user->mobileNo,            
            'email' => strtolower($user->email),                        
        ];
        if (!empty($user->password)) {
            $data['password'] = $user->password;
        }
        if (!empty($user->activationCode)) {
            $data['"activationCode"'] = $user->activationCode;
        }
        
        
        if ($user->ID == NULL) {
			$data['"isActive"'] = 0;
            //$data['"createdAt"'] = $user->createdAt;
            $ret = $this->getAdapter()->insert($this->entityTable, $this->setCreatedDate($data))->getLastInsertId('"User_ID_seq"'); //return id

            if ($ret > 0) {
                $user->ID = $ret;
                return TRUE;
            }
        } else {
			if($user->isActive){
				$data['"isActive"'] =1;
			}
            $ret = $this->getAdapter()->update($this->entityTable, $this->setModifiedDate($data), '"ID"=' . intval($user->ID));
            if ($ret > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * update last login of the user
     * @param User $user
     * return boolean
     */
    public function updateLastLogin(User &$user)
    {
        $user->lastAccess = TimeHelper::get_current_time();
        $data = [
            '"lastAccess"' => $user->lastAccess,
            //'"lastLoginIPAddress"' => $user->lastLoginIPAddress,
            //'"deviceToken"' => $user->deviceToken,
            //'"deviceAuthToken"' => $user->deviceAuthToken,
        ];
        $ret = $this->getAdapter()->update($this->entityTable, $this->setModifiedDate($data), '"ID"=' . intval($user->ID));
        if ($ret > 0) {
            return TRUE;
        }
        return FALSE;
    }


    public function updateForgottenPasswordCode(User &$user)
    {
        $data = [
            '"forgottenPasswordCode"' => $user->forgottenPasswordCode,
            '"forgottenPasswordTime"' => $user->forgottenPasswordTime,
        ];
        $ret = $this->getAdapter()->update($this->entityTable, $this->setModifiedDate($data), '"ID"=' . intval($user->ID));
        if ($ret > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function clearForgottenPasswordCode(User &$user)
    {
        $data = [
            '"forgottenPasswordCode"' => '',
            '"forgottenPasswordTime"' => NULL,
        ];
        $ret = $this->getAdapter()->update($this->entityTable, $this->setModifiedDate($data), '"ID"=' . intval($user->ID));
        if ($ret > 0) {
            return TRUE;
        }
        return FALSE;
    }

    

    public function updateAvatar($ID, $avatarID)
    {
        $adapter = $this->getAdapter();

        $data = [
            '"avatarID"' => $avatarID
        ];

        $ret = $this->getAdapter()->update2($this->entityTable, $this->setModifiedDate($data), ['"ID"' => $ID]);
        if ($ret > 0) {
            return TRUE;
        }

        return FALSE;
    }
    
    public function findByEmail($email)
    {
		$adapter = $this->getAdapter();
		$adapter->select($this->entityTable,
            array('"email"' => $email));

        if (!$row = $adapter->fetch()) {
            return null;
        }

        return $this->createEntity($row);
    }
}
