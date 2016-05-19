<?php
namespace ksf;

class DataBank {
    public $result;
 
    function __construct() {
        $this->result = [];
    }
    
    
    public function __get($name) {
        if(!isset($this->result[$name])) return false;
        return $this->result[$name];
    }
    
    public function __set($name, $val) {
        $this->result[$name] = $val;
    }
    
    public function is_set($name) {
        return isset($this->result[$name]);
    }
}
?>