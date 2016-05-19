<?php
namespace ksf;

class Router extends Singleton {
    
    public function findAction() {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $path = explode("/", trim($parsed_url["path"], "/"));
        if(count($path) > 0)
            if(strtolower($path[0]) == "admin") { // админка
                if(count($path) > 1 && file_exists($file = dirname(dirname(__DIR__))."/design/admin/".  ucfirst($path[1]) ."Controller.php")) {
                    include $file;
                    $urls = call_user_func(ucfirst($path[1])."Controller::getLinks");
                    if($result = $this->checkURLs($urls, 2)) return array_merge($result, ["controller" => ucfirst($path[0]) ."Controller.php", "file" => $file, "type" => "admin"]);                    
                } elseif(count($path) > 1 && file_exists($file = dirname(dirname(__DIR__))."/modules/".  strtolower($path[1]) ."/AdminController.php")) { // контроллер страниц модуля
                    include $file;
                    $urls = \AdminController::getLinks();
                    if($result = $this->checkURLs($urls, 2)) return array_merge($result, ["controller" => "AdminController", "file" => $file, "type" => "admin"]);
                }
                if(file_exists($file = dirname(dirname(__DIR__))."/design/admin/".   ucfirst(System::getInstance()->getParameter("DefaultAdminController")) ."Controller.php")) { // стандартный контроллер
                    include $file;
                    $urls = call_user_func(ucfirst(System::getInstance()->getParameter("DefaultAdminController"))."Controller::getLinks");
                    if($result = $this->checkURLs($urls, true)) return array_merge($result, ["controller" => ucfirst(System::getInstance()->getParameter("DefaultAdminController"))."Controller", "file" => $file, "type" => "admin"]);
                }
            } elseif(file_exists($file = dirname(dirname(__DIR__))."/design/frontend/".  ucfirst($path[0]) ."Controller.php")) { // контроллер
                include $file;
                $urls = call_user_func(ucfirst($path[0])."Controller::getLinks");
                if($result = $this->checkURLs($urls, true)) return array_merge($result, ["controller" => ucfirst($path[0]) ."Controller.php", "file" => $file, "type" => "frontend"]);
            } elseif(file_exists($file = dirname(dirname(__DIR__))."/modules/".  strtolower($path[0]) ."/PagesController.php")) { // контроллер страниц модуля
                include $file;
                $urls = \PagesController::getLinks();
                if($result = $this->checkURLs($urls, true)) return array_merge($result, ["controller" => "PagesController", "file" => $file, "type" => "frontend"]);
            }
        if(file_exists($file = dirname(dirname(__DIR__))."/design/frontend/".   ucfirst(System::getInstance()->getParameter("DefaultController")) ."Controller.php")) { // стандартный контроллер
            include $file;
            $urls = call_user_func(ucfirst(System::getInstance()->getParameter("DefaultController"))."Controller::getLinks");
            if($result = $this->checkURLs($urls)) return array_merge($result, ["controller" => ucfirst(System::getInstance()->getParameter("DefaultController"))."Controller", "file" => $file, "type" => "frontend"]);
        }
        return $this->return404();
    }
    
    private function checkURLs($urls, $isNameInURL = false) {
        $parsed_url = trim(parse_url($_SERVER['REQUEST_URI'])["path"], "/");
        if($isNameInURL) {
            $parsed_url = explode("/", $parsed_url);
            unset($parsed_url[0]);
            if($isNameInURL === 2) unset($parsed_url[1]);
            $parsed_url = "/".trim(implode("/", $parsed_url), "/");
        } else $parsed_url = "/".$parsed_url;
        foreach($urls as $key => $val) {
            preg_match_all("/\{([a-z])\:([a-zA-Z\_\-]+)\}/", $key, $subject, PREG_SET_ORDER);
            $names = [];
            
            $key = str_replace(["/", "+"], ["\/", "\+"], $key);
            foreach($subject as $value) {
                $names[] = $value[2];
                switch($value[1]) {
                    case "s":
                        $key = str_replace($value[0], "([a-zA-Z0-9\_\-\.\(\)\,\+]+)", $key);
                    break;
                    case "i":
                        $key = str_replace($value[0], "([0-9]+)", $key);
                    break;
                    case "d":
                        $key = str_replace($value[0], "([0-9\.\,]+)", $key);
                    break;
                    case "f":
                        $key = str_replace($value[0], "f:([a-zA-Z0-9\_\-\.\(\)\,\+\;\:\=]+)", $key);
                    break;
                }
            }
            
            if(preg_match_all("/^".$key."$/", $parsed_url, $subject, PREG_SET_ORDER)) {
                if(is_array($val)) { // "url" => ["sql", [function ,[params]], ...]
                    foreach($val[1][1] as $k => $param)
                        if(($res = array_search($param, $names))+1)
                            $val[1][1][$k] = $subject[0][$res+1];
                    $sql = DatabaseHandler::GetRow($val[0], $val[1][1]);
                    $sql = call_user_func($val[1][0], $sql);
                    if(is_array($val[2])) { // ... = ["url", "actionTrue"], ["url", "actionFalse"]
                        if($sql) $key = $val[2];
                        else $key = $val[3];
                        preg_match_all("/\{([a-z])\:([a-zA-Z]+)\}/", $key[0], $subject, PREG_SET_ORDER);
                        foreach($subject as $value)
                            $names[] = $value[2];   
                        $params = [];
                        for($i = 1; $i < count($subject[0]); $i++)
                            $params[$names[$i-1]] = $subject[0][$i];     
                        return ["action" => $key[1], "parameters" => $params];
                    } else {
                        $params = [];
                        for($i = 1; $i < count($subject[0]); $i++)
                            $params[$names[$i-1]] = $subject[0][$i];
                        return ["action" => ($sql) ? $val[2] : $val[3], "parameters" => $params];
                    }
                } else {
                    $params = [];
                    for($i = 1; $i < count($subject[0]); $i++)
                        $params[$names[$i-1]] = $subject[0][$i];
                    return ["action" => $val, "parameters" => $params];
                }
            }
        }
        return false;
    }
    
    public function return404() {
        header("HTTP/1.0 404 Not Found");
        return ["controller" => ucfirst(System::getInstance()->getParameter("DefaultController"))."Controller", "action" => "page404", "parameters" => [], $file = dirname(dirname(__DIR__))."/design/frontend/".ucfirst(System::getInstance()->getParameter("DefaultController"))."Controller.php"];
    }
}

