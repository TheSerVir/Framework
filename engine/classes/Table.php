<?php
namespace ksf;

class Table {
    
    public $tableName;
  
    function __construct($tableName) {
        $this->tableName = $tableName;  
    }
    
    function select($what = "", $where = "", $post = "") {
        if(is_array($what)) $what = implode(", ", $what);
        if(is_array($where)) {
            $where1 = array();
            foreach($where as $key=>$val) {
                if(strlen($val) == 0)
                    $where1[] = $key;
                else 
                    $where1[] = "`" . $key . "` = '".$val."'";
            }
            $where = implode(" AND ", $where1);
            unset($where1);
        }
        if(($what == "" || $what == "*") && $where == "") return $this->selectQuery();
        if(($what == "" || $what == "*") && $where != "") return $this->selectQuery("*", $where);
        if($what != "" && $where == "") return $this->selectQuery($what);        
        return $this->selectQuery($what, $where, $post);
    }
    
    function selectQuery($what = "*", $where = "", $post = "") {
        return DatabaseHandler::GetAll("SELECT ".$what." FROM `".$this->tableName."`" . ( ($where == "") ? "" : " WHERE ".$where) . " ".$post );
    }
    
    function insert($what = array()) {
        if(!is_array($what)) return false;
        
        $keys = array();
        $values = array();
        foreach($what as $key=>$val) {
            $keys[] = $key;
            $values[] = $val;
        }
        $keys = implode(", ", $keys);
        $values = "'".implode("', '", $values)."'";
        
        if(count($what) > 0) return $this->insertQuery($keys, $values);
        return false;
    }
    
    function insertQuery($keys, $values) {
        return DatabaseHandler::Execute("INSERT INTO `".$this->tableName."` (".$keys.") VALUES (".$values.")");
    }
    
    function update($what = array(), $where = "") {
        if(!is_array($what) || count($what) == 0) return false;
        $temp = [];
        foreach($what as $key=>$val) {
            $temp[] = "`".$key."` = '".$val."'";
        }
        $what = implode(", ", $temp);
        if(is_array($where) && count($where) > 0) {
            $temp = [];
            foreach($where as $key=>$val) {
                $temp[] = "`".$key."` = '".$val."'";
            }
            $where = implode(", ", $temp);
        }
        if(strlen($where) > 0) return $this->updateQuery($what, $where);
        return $this->updateQuery($what);
    }
    
    function updateQuery($what, $where = "") {
        return DatabaseHandler::Execute("UPDATE `".$this->tableName."` SET " . $what . ( ($where == "") ? "" : " WHERE ".$where));
    }
    
}
?>