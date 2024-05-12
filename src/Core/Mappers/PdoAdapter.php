<?php
namespace Core\Mappers;
use \PDO;
//https://www.sitepoint.com/integrating-the-data-mappers/
//FOR POSTGRESQL

class PdoAdapter implements DatabaseAdapterInterface
{
    protected $config = array();
    protected $connection = NULL;
    protected $statement;
    protected $fetchMode = PDO::FETCH_ASSOC;   
    protected $app;
    protected $inTransaction = FALSE;
    protected $hasException = FALSE;
    
    public function __construct($app){
		$this->app = $app;
    }
    function __destruct() {
        $this->statement = NULL;
        $this->connection = NULL;
    }
    public function getConnection(){
	$this->connect();
	return $this->connection;
    }
    public function getStatement() {
        if ($this->statement === null) {
            throw new \PDOException(
              "There is no PDOStatement object for use.");
        } 
        return $this->statement;
    }
    
    public function connect() {
		//transaction
		if(isset($this->app['use_transaction']) && $this->app['use_transaction']==TRUE){
			//no db connection then create and save to app
			if($this->app['db_transaction']==NULL){
				try {
					$this->connection = $this->app['pdo.factory']();
					$this->connection->setAttribute(PDO::ATTR_ERRMODE,
						PDO::ERRMODE_EXCEPTION);
					$this->connection->setAttribute(
						PDO::ATTR_EMULATE_PREPARES, false);                 
					$this->connection->setAttribute(
						PDO::ATTR_STATEMENT_CLASS, array('Core\Mappers\MyPDOStatement', array()));                 
							
					$this->app['db_transaction'] = 	$this->connection;
				}
				catch (\PDOException $e) {
					throw new \RunTimeException($e->getMessage().'. '.$this->app['pdo.dsn']);
				}
				if($this->connection->beginTransaction()){					
					$this->inTransaction = TRUE;
				}
			}else{
				$this->connection = $this->app['db_transaction'];
			}
			
		}else{
			// if there is a PDO object already, return early
			if ($this->connection) {
				return;
			}
	 
			try {
				$this->connection = $this->app['pdo.factory']();
				$this->connection->setAttribute(PDO::ATTR_ERRMODE,
					PDO::ERRMODE_EXCEPTION);
				$this->connection->setAttribute(
					PDO::ATTR_EMULATE_PREPARES, false);                 
			}
			catch (\PDOException $e) {
				throw new \RunTimeException($e->getMessage().'. '.$this->app['pdo.dsn']);
			}
		}
		
    }
    
    public function disconnect() {
        if(isset($this->app['use_transaction']) && $this->app['use_transaction']==TRUE){

        }else{
            $this->statement = NULL;
            $this->connection = NULL;
        }

    }
    public function query($sql){
        $this->connect();

        try {
            $this->statement = NULL;
            $this->statement = $this->connection->query($sql);

            return $this;
        }
        catch (\PDOException $e) {
            if($this->inTransaction){
                $this->connection->rollBack();
            }
            $this->handleException($e);
        }
    }
    public function prepare($sql, array $options = array()) {
        $this->connect();
        
        try {
            $this->statement = NULL;
            $this->statement = $this->connection->prepare($sql,
                $options);
                
            return $this;
        }
        catch (\PDOException $e) {
			if($this->inTransaction){
				$this->connection->rollBack();
			}
            $this->handleException($e);
        }
    }
    
    public function execute(array $parameters = array()) {
        try {
			$ret = $this->getStatement()->execute($parameters);
            
			if(!$ret){
				print_r($this->connection->errorInfo());			
			}
            return $this;
        }
        catch (\PDOException $e) {
			if($this->inTransaction){
				$this->connection->rollBack();
			}
			$this->handleException($e);
        }
    }
    public function handleException($e){
	$msg = $e->getMessage().'<br />SQL: '. $this->statement->queryString;
	throw new \RunTimeException($msg);
    }

    public function countAffectedRows() {
        try {
            return $this->getStatement()->rowCount();
        }
        catch (\PDOException $e) {
			if($this->inTransaction){
				$this->connection->rollBack();
			}
            throw new \RunTimeException($e->getMessage());
        }
    }

    public function getLastInsertId($name = null) {
        $this->connect();
        return $this->connection->lastInsertId($name);
    }
    
    public function fetch($fetchStyle = null,
        $cursorOrientation = null, $cursorOffset = null) {
        if ($fetchStyle === null) {
            $fetchStyle = $this->fetchMode;
        }
 
        try {
            return $this->getStatement()->fetch($fetchStyle, 
                $cursorOrientation, $cursorOffset);
        }
        catch (\PDOException $e) {
			if($this->inTransaction){
				$this->connection->rollBack();
			}
            $this->handleException($e);
        }
    }
     
    public function fetchAll($fetchStyle = null, $column = 0) {
        if ($fetchStyle === null) {
            $fetchStyle = $this->fetchMode;
        }
 
        try {
            return $fetchStyle === PDO::FETCH_COLUMN
               ? $this->getStatement()->fetchAll($fetchStyle, $column)
               : $this->getStatement()->fetchAll($fetchStyle);
        }
        catch (\PDOException $e) {
			if($this->inTransaction){
				$this->connection->rollBack();
			}
            $this->handleException($e);
        }
    }
    
    public function select($table, array $bind = array(), 
        $boolOperator = "AND", $order = array(), $limit = "", $offset="") {
        if ($bind) {
            $where = array();
            foreach ($bind as $col => $value) {
                unset($bind[$col]);
                $cleanCol = str_replace('"', '', $col);
                $bind[":" . $cleanCol] = $value;
                $where[] = $col . " = :" . $cleanCol;
            }
        }
 
        $sql = "SELECT * FROM " . $table
            . (($bind) ? " WHERE "
            . implode(" " . $boolOperator . " ", $where) : " ");

        if($order && ($norder = count($order))>0){
            $sql .= " ORDER BY ";
            $iorder = 0;
            foreach($order as $k=>$v){
                $sql .= $k.' '.$v.' ';
                
                if($iorder<($norder-1)){
                    $sql .= ', ';
                }
                $iorder++;
            }
            
            //echo $sql;
        }
        if(!empty($limit)){
            $sql .= " LIMIT " . $limit;
        }
        if(!empty($offset)){
            $sql .= " OFFSET " . $offset;
        }

        //echo $sql;
        $ret = $this->prepare($sql)
            ->execute($bind);
		
        return $this;
    }

    public function insert($table, array $bind) {
        $cols = implode(", ", array_keys($bind));

        foreach ($bind as $col => $value) {
            unset($bind[$col]);
            $cleanCol = str_replace('"', '', $col);
            $bind[":" . $cleanCol] = $value;
        }
		$values = implode(", ", array_keys($bind));
        $sql = "INSERT INTO " . $table
            . " (" . $cols . ")  VALUES (" . $values . ")";
        
        
        return  $this->prepare($sql)
            ->execute($bind)
            //->getLastInsertId()            
            ;
    }
    
    public function update($table, array $bind, $where = "") {
        $set = array();
        
        foreach ($bind as $col => $value) {
            unset($bind[$col]);
            $cleanCol = str_replace('"', '', $col);
            
            $bind[":" . $cleanCol] = $value;
            $set[] = $col . " = :" . $cleanCol;
        }
 
        $sql = "UPDATE " . $table . " SET " . implode(", ", $set)
            . (($where) ? " WHERE " . $where : " ");
            
        return $this->prepare($sql)
            ->execute($bind)
            ->countAffectedRows();
    }
    public function update2($table, array $bind, array $where) {
        $set = array();        
        foreach ($bind as $col => $value) {
            unset($bind[$col]);
            $cleanCol = str_replace('"', '', $col);
            
            $bind[":" . $cleanCol] = $value;
            $set[] = $col . " = :" . $cleanCol;
        }
        
        $set2 = array();
        foreach ($where as $col => $value) {
            unset($where[$col]);
            $cleanCol = str_replace('"', '', $col);
            
            $bind[":" . $cleanCol] = $value;
            $set2[] = $col . " = :" . $cleanCol;
        }
 
        $sql = "UPDATE " . $table . " SET " . implode(", ", $set)
            . (count($set2)>0 ? " WHERE " . implode(" AND ", $set2) : "  ");
            
		//echo $sql;exit();			
        return $this->prepare($sql)
            ->execute($bind)
            ->countAffectedRows();
    }
    
    public function delete($table, $where = "") {
        $sql = "DELETE FROM " . $table . (($where) ? " WHERE " . $where : " ");
        return $this->prepare($sql)
            ->execute()
            ->countAffectedRows();
    }
    
    public function delete2($table, array $where) {
		$set2 = array();
        foreach ($where as $col => $value) {
            unset($where[$col]);
            $cleanCol = str_replace('"', '', $col);
            
            $bind[":" . $cleanCol] = $value;
            $set2[] = $col . " = :" . $cleanCol;
        }
        $sql = "DELETE FROM " . $table . (count($set2)>0 ? " WHERE " . implode(" AND ", $set2) : "  ");
        return $this->prepare($sql)
            ->execute($bind)
            ->countAffectedRows();
    }
    
    public function isInTransaction(){
		return $this->inTransaction;
	}
	public function commitTransaction(){
		if(isset($this->app['use_transaction']) && $this->app['use_transaction']==TRUE){
			if($this->app['db_transaction']!=NULL){
				$this->app['db_transaction']->commit();
			}
		}
	}
	public  function execFunction($name, $args, $order=array()){
        $_params = array();
        if (count($args)>0) {
            for ($i=0; $i<count($args); $i++) {
                $_params[] = '?';
            }
        }

        $sql = "SELECT * FROM " . $name . "(" . implode(', ', $_params) .  ")";
		if($order && ($norder = count($order))>0){
            $sql .= " ORDER BY ";
            $iorder = 0;
            foreach($order as $k=>$v){
                $sql .= $k.' '.$v.' ';
                
                if($iorder<($norder-1)){
                    $sql .= ', ';
                }
                $iorder++;
            }
            
            //echo $sql;
        }
        $ret = $this->prepare($sql)
            ->execute($args);

        return $this;

    }

    public function getRows($sql,$args){
        $ret = $this->prepare($sql)
            ->execute($args);
        $rows = $this->fetchAll();
        return $rows;
    }
    public function getRow($sql,$args){
        $ret = $this->adapter->prepare($sql)
            ->execute($args);
        $row = $this->fetch();
        return $row;
    }
    public function getCount($table,$bind=array(), $boolOperator="AND"){

        if ($bind) {
            $where = array();
            foreach ($bind as $col => $value) {
                unset($bind[$col]);
                $cleanCol = str_replace('"', '', $col);
                $bind[":" . $cleanCol] = $value;
                $where[] = $col . " = :" . $cleanCol;
            }
        }

        $sql = "SELECT count(*) as c FROM " . $table
            . (($bind) ? " WHERE "
                . implode(" " . $boolOperator . " ", $where) : " ");

        $ret = $this->prepare($sql)
            ->execute($bind);
        $row = $this->fetch();
        if($row){
            return $row['c'];
        }
        return 0;
    }


}
