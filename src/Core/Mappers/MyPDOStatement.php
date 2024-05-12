<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\Mappers;

/**
 * Description of MyPDOStatement
 *
 * @author aldo
 */
class MyPDOStatement extends \PDOStatement
{
  protected $_debugValues = null;

  protected function __construct()
  {
    // need this empty construct()!
  }

  public function execute($values=array())
  {
    if (count($values) !== count($values, COUNT_RECURSIVE)){
	echo 'invalid parameter';
	var_dump($values);
    } 
    $this->_debugValues = $values;
    
    try {
      $t = parent::execute($values);
      // maybe do some logging here?
    } catch (\PDOException $e) {
      // maybe do some logging here?
      throw $e;
    }

    return $t;
  }

  public function _debugQuery($replaced=true)
  {
    $q = $this->queryString;

    if (!$replaced) {
      return $q;
    }

    return preg_replace_callback('/:([0-9a-z_]+)/i', array($this, '_debugReplace'), $q);
  }

  protected function _debugReplace($m)
  {
    $v = $this->_debugValues[$m[1]];
    if ($v === null) {
      return "NULL";
    }
    if (!is_numeric($v)) {
      $v = str_replace("'", "''", $v);
    }

    return "'". $v ."'";
  }
}
