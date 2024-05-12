<?php

namespace Corn\Entities;
use Core\Entities\BaseEntity;

class SaveMoistureDataForm extends BaseEntity{	
	public $fileName;
	public $location;
	public $percentage;
	public $areaType;
	public $latitude;
	public $longitude;
	public $recordDate;
	public $recordMethodType;
	
	public function validate(){
		$this->required([ 'percentage', 'areaType','latitude', 'longitude', 'recordDate']);
		
		return !$this->has_error();
	}
}
