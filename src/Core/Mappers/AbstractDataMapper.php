<?php
namespace Core\Mappers;
use Core\Helpers\TimeHelper;

abstract class AbstractDataMapper
{
    protected $adapter;
    protected $entityTable;

    public function __construct(DatabaseAdapterInterface $adapter) {
        $this->adapter = $adapter;
    }
    function __destruct() {
        //bug: http://php.net/manual/en/pdo.connections.php#114822
        $this->adapter->disconnect();
    }
    
    public function getAdapter() {
        return $this->adapter;
    }

    public function findById($id)
    {
        $this->adapter->select($this->entityTable,
            array('"ID"' => $id));

        if (!$row = $this->adapter->fetch()) {
            return null;
        }

        return $this->createEntity($row);
    }

    public function findAll(array $conditions = array(), $boolOperator = "AND",  $order = "", $limit = "")
    {
        $entities = NULL;
        $this->adapter->select($this->entityTable, $conditions, $boolOperator, $order, $limit);
        $rows = $this->adapter->fetchAll();

        if ($rows) {
			$entities = array();
            foreach ($rows as $row) {
                $entities[] = $this->createEntity($row);
            }
        }

        return $entities;
    }

    // Create an entity (implementation delegated to concrete mappers)
    abstract protected function createEntity(array $row);
    
    public function delete($id)
    {
        return $this->adapter->delete($this->entityTable,
            '"ID"='.intval($id));

    }
    //http://stackoverflow.com/a/15875555/1225672
    protected function guidv4()
    {
        $data = random_bytes(16);
        assert(strlen($data) == 16);
    
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    public function setModifiedDate(array &$arr){
        $arr['"updatedAt"'] = TimeHelper::get_time_in_utc();
        return $arr;
    }
    public function setCreatedDate(array &$arr){
        $arr['"createdAt"'] = TimeHelper::get_time_in_utc();
        return $arr;
    }
    //https://stackoverflow.com/a/25550140/1225672
    /*
    protected function _findRange($tableName, $minColumnName, $maxColumnName, $minValue, $maxValue){
        $prm = [':minValue' => $minValue, ':maxValue' => $maxValue];
        $sql = sprintf(' FROM %s WHERE :minValue <= %s AND :maxValue >= %s ', $tableName,$maxColumnName,$minColumnName);
        $ret = $this->adapter->prepare('select * '.$sql)
            ->execute($prm);
        $row = $this->adapter->fetch();
        if($row){
            return $this->createEntity($row);
        }
        return NULL;
    }*/
}
