<?php

namespace Corn\Mappers;

use Core\Helpers\TimeHelper;
use Core\Mappers\AbstractDataMapper;
use Core\Entities\PagingResult;
use Core\Entities\Paging;
use Core\Mappers\DatabaseAdapterInterface;
use Corn\Entities\MoistureData;
use Corn\Entities\SearchMoistureDataForm;

class MoistureDataMapper extends AbstractDataMapper
{

    protected $entityTable = '"MoistureData"';

    public function __construct(DatabaseAdapterInterface $adapter)
    {
        parent::__construct($adapter);
    }

    /**
     * abstract method
     */
    protected function createEntity(array $row)
    {
        $entity = new MoistureData();
        $entity->bind($row);
        return $entity;
    }
    
    public function save(MoistureData &$entity){
		
		$data = [
            '"fileName"' => $entity->fileName,
            '"longitude"' => $entity->longitude,
            '"percentage"' => $entity->percentage,            
            '"UserID"' => $entity->UserID,     
            '"locationName"' => $entity->location,            
            '"latitude"' => $entity->latitude,            
            '"recordDate"' => $entity->recordDate,            
            '"areaType"' => $entity->areaType,    
            '"recordMethodType"' => $entity->recordMethodType,                
                               
        ];
        
        
        if ($entity->ID == NULL) {			            
            $ret = $this->getAdapter()->insert($this->entityTable, $this->setCreatedDate($data))->getLastInsertId('"MoistureData_ID_seq"'); //return id

            if ($ret > 0) {
                $entity->ID = $ret;
                return TRUE;
            }
        } else {			
            $ret = $this->getAdapter()->update($this->entityTable, $this->setModifiedDate($data), '"ID"=' . intval($user->ID));
            if ($ret > 0) {
                return TRUE;
            }
        }
        return FALSE;
	}
	
	public function findByFileName($fileName)
    {
		$adapter = $this->getAdapter();
		$adapter->select($this->entityTable,
            array('"fileName"' => $fileName));

        if (!$row = $adapter->fetch()) {
            return null;
        }

        return $this->createEntity($row);
    }
    
    public function search($userId, SearchMoistureDataForm $form){
		//var_dump($form);
		$conditions = [];
        //$filter = $paging->getFilter();
        //$order = $paging->getSort();
        $paging = new Paging();
        $result = new PagingResult();

        $entities = array();
        //get data
        $sql = '';
        $prm = [];
        //filter rows
        $sqlfilter = sprintf(' from %s where 1=1 ',$this->entityTable);        
        
		$arrsqlfilter = [];
		if(!empty($form->locationName)){
			 $arrsqlfilter[] =  '  "locationName" ILIKE :locationName ';
			 $prm[':locationName'] = '%'.$form->locationName.'%';
		}
		
		if(!empty($form->fileName)){
			 $arrsqlfilter[] =  ' "fileName" ILIKE :fileName ';
			 $prm[':fileName'] = '%'.$form->fileName.'%';
		}
		if(!empty($form->recordDate)){
			 $arrsqlfilter[] =  '  "recordDate" BETWEEN :recordDateStart AND :recordDateEnd';
			 $prm[':recordDateStart'] = "'".$form->recordDate." 00:00:00'";
			 $prm[':recordDateEnd'] = "'".$form->recordDate." 23:59:59'";
		}
		if(count($arrsqlfilter)>0){
			$t = implode(' OR ', $arrsqlfilter);
			$sqlfilter .= ' AND '.$t;
		}
		
		

        $sql = $sqlfilter;
        //echo $sql;
        //exit();
		$sql .= ' ORDER BY "ID" desc ';
        
        $limit = $paging->getPageSize();
        if(!empty($limit)){
            $sql .= " LIMIT " . $limit;
        }
        $offset =  $paging->getStart();
        if(!empty($offset)){
            $sql .= " OFFSET " . $offset;
        }
        //echo $sql;    
        $bind = NULL;
        $ret = $this->adapter->prepare('select * '.$sql)
            ->execute($prm);
        
        $rows = $this->adapter->fetchAll();

        if ($rows) {
            foreach ($rows as $row) {
                $entities[] = $row;
            }
            $result->setData($entities);

            //total            
            $ret = $this->adapter->prepare('select count(*) as c '.$sqlfilter)
                ->execute($prm);
            $row = $this->adapter->fetch();
            if($row){
                $n = $row['c'];
            } 
            $result->setTotalRecords($n);
            $result->calculate($paging);

        }

        return $result;
	}
}
