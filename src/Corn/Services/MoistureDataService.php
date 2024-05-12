<?php
namespace Corn\Services;

use Pimple\Container;
use Configs\GlobalConfig;

use Core\Services\ServiceException;
use Corn\Mappers\MoistureDataMapper;
use Corn\Entities\SaveMoistureDataForm;
use Corn\Entities\SearchMoistureDataForm;
use Corn\Entities\MoistureData;


class MoistureDataService
{   private $app;

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
            $this->mapper = new MoistureDataMapper($this->app['pdo_adapter']);
        }
        return $this->mapper;
    }
    
    public function save($userId, SaveMoistureDataForm $form){
		$mapper = $this->getMapper();
		$prev = $mapper->findByFileName($form->fileName);
		if($prev!=NULL){
			throw new ServiceException('file name already exist');
		}
		$entity = new MoistureData();
		$entity->bind($form);
		$entity->UserID = $userId;
		return $mapper->save($entity);
	}
	
	public function search($userId, SearchMoistureDataForm $form){
		$mapper = $this->getMapper();
		$prev = $mapper->search($userId, $form);
		
		return $prev;
	}
	
	
}
