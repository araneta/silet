<?php

namespace Corn\Entities;
use Core\Entities\BaseEntity;

class MoistureData extends BaseEntity{
	public $ID;
	public $UserID;
	public $fileName;
	public $location;
	
	public $percentage;
	public $areaType;
	public $latitude;
	public $longitude;
	public $recordDate;
	
	public function validate(){
		$this->required(['percentage', 'areaType','latitude', 'longitude', 'recordDate']);
		
		return !$this->has_error();
	}
}
