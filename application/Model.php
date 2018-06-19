<?php

class Model
{
    protected $_db;
    
    public function __construct() 
    {
        $this->_db = DatabasePDO::getInstance();
    }
    
    
    public function beginTransaction() {
        return $this->_db->beginTransaction();
    }

    public function commit() {
        return $this->_db->commit();
    }

    public function rollback() {
        return $this->_db->rollback();
    }
}
?>
