<?php

namespace Corn\Entities;
use Core\Entities\BaseEntity;

class SearchMoistureDataForm extends BaseEntity{	
	public $fileName;
	public $locationName;
	public $recordDate;
	
	
	public function validate(){
		$this->required([ 'fileName', 'location','recordDate']);
		
		return !$this->has_error();
	}
}
