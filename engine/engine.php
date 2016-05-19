<?php
namespace ksf;
include "functions.php";
include "patterns/singleton.php";

class System extends Singleton {
    
    private $configs;

    public function init() {
        $this->loadEngine();
        $action = Router::getInstance()->findAction();
        $controller = new $action["controller"]();
        $controller->setParameters($action["parameters"]);
        $function = $action["action"]."Action";
        $controller->$function();
        $data = $controller->getData();
        $tmp = TemplateHandler::getInstance();
        $tmp->setParameters($data, $action["controller"], $action["action"], $action["type"], $action["file"]);
        $tmp->start();
    }
    
    public function loadEngine() {
        $this->configs = getConfigs();
        
        $classes = scandir(__DIR__.'/classes');
        foreach($classes as $class)
            if($class != '.' && $class != '..')
                include "classes/".$class;
        
        $interfaces = scandir(__DIR__.'/interfaces');
        foreach($interfaces as $interface)
            if($interface != '.' && $interface != '..')
                include "interfaces/".$interface;
    }
    
    public function getParameter($name) {
        return $this->configs[$name];
    }
    
}

