<?php
namespace ksf;

class Database {
    
    public function __construct() {
    }
    
    public function __get($tableName) {
        if(count(DatabaseHandler::GetAll("SHOW TABLES LIKE :tableName", array(":tableName" => $tableName))) == 1) return new Table($tableName);
        return null;
    }
    
    public function getAll($sql, $params = null) {
        return DatabaseHandler::GetAll($sql, $params);
    }
    
    public function getRow($sql, $params = null) {
        return DatabaseHandler::GetRow($sql, $params);
    }
    
    public function execute($sql, $params = null) {
        return DatabaseHandler::Execute($sql, $params);
    }
}
?>