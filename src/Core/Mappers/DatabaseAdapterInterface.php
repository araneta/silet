<?php
namespace Core\Mappers;

interface DatabaseAdapterInterface
{
    public function connect();
    public function disconnect();
    public function query($sql);
    public function prepare($sql, array $options = array());
    public function execute(array $parameters = array());

    //execute function
    public function execFunction($name, $args);
    
    public function fetch($fetchStyle = null, 
        $cursorOrientation = null, $cursorOffset = null);
    public function fetchAll($fetchStyle = null, $column = 0);
    
    public function select($table, array $bind, 
        $boolOperator = "AND",  $order = NULL, $limit = "", $offset="");
    public function insert($table, array $bind);
    
    public function update($table, array $bind, $where = "");
    public function update2($table, array $bind, array $where);
    
    public function delete($table, $where = "");
    public function delete2($table, array $where);

}
