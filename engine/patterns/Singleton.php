<?php
namespace ksf;

class Singleton {

    final public static function getInstance()
    {
        static $instances = array();
        $calledClass = get_called_class();
        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass();
        }
        return $instances[$calledClass];
    }
    
    private function __construct() {
        // приватный конструктор ограничивает реализацию getInstance ()
    }
    
    private function __sleep() {
        trigger_error('Сериализация запрещена.', E_USER_ERROR);
    }

    public function __clone()
    {
        trigger_error('Клонирование запрещено.', E_USER_ERROR);
    }

    public function __wakeup()
    {
        trigger_error('Десериализация запрещена.', E_USER_ERROR);
    }
}