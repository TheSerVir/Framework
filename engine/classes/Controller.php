<?php
namespace ksf;

class Controller {
    
    protected $p = [];
    protected $databank;
    protected $settings;

    public function __construct() {
        $this->databank = [];
        $this->settings = ["layout" => null, "action" => null];
    }
    
    public function setParameters($array = null) {
        if(!is_null($array)) $this->p = $array;
    }
    
    function __set($name, $value) {
        $this->databank[$name] = $value;
    }
    
    function __get($name) {
        return $this->databank[$name];
    }
    
    function getData() {
        return ["variables" => $this->databank, "settings" => $this->settings];
    }
    
    function setNewActionTemplate($name) {
        $this->settings["action"] = $name;
    }
    
    function setLayout($name) {
        $this->settings["layout"] = $name;
    }
    
    function page404Action() {
        echo "Страница не найдена";
    }
}
