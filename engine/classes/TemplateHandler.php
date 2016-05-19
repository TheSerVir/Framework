<?php
namespace ksf;

class TemplateHandler extends Singleton {
    
    private $settings = NULL;
    private $variables = NULL;
    
    public function setParameters($data, $controller, $action, $type, $file) {
        $this->settings = $data["settings"];
        $this->variables = $data["variables"];
        if(is_null($this->settings["action"])) $this->settings["action"] = $action;
        if(is_null($this->settings["layout"])) $this->settings["layout"] = ($type == "admin") ? System::getInstance()->getParameter("DefaultAdminLayout") 
                                                                                              : System::getInstance()->getParameter("DefaultLayout");
        $this->settings["controller"] = strtolower(str_replace("Controller", "", $controller));
        $this->settings["file"] = $file;
        $this->settings["type"] = $type;
    }
    
    public function start() {
        if(is_null($this->settings))
            throw new \Exception("Parameters don't set");
        
        // layout
        $_lay = dirname(dirname(__DIR__))."/design/".$this->settings["type"]."/layouts/" . $this->settings["layout"] . ".html";
        $_lay_c = dirname(dirname(__DIR__)) . "/compilation/" . md5($this->settings["type"].$this->settings["layout"]) . "_" . $this->settings["type"] . "_" . $this->settings["layout"] . ".php";
        if(!file_exists($_lay_c) || filemtime($_lay) >= filemtime($_lay_c)) {
            $template = $this->compile(\file_get_contents($_lay), $_lay);
            \file_put_contents($_lay_c, $template);
        }
        
        // controller
        $_tmp = dirname($this->settings["file"])."/views/" . $this->settings["controller"] . "/" . $this->settings["action"] . ".html";
        $_tmp_c = dirname(dirname(__DIR__)) . "/compilation/" . md5(dirname($this->settings["file"])) . "_" . $this->settings["type"] . "_" . $this->settings["controller"] . "_" . str_replace("/", "_", $this->settings["action"]) . ".php";
        if(!file_exists($_tmp_c) || filemtime($_tmp) >= filemtime($_tmp_c)) {
            $template = $this->compile(\file_get_contents($_tmp), $_tmp);
            \file_put_contents($_tmp_c, $template);
        }
        
        if(!is_null($this->variables)) extract($this->variables);
        
        // layout
        ob_start();
        include $_lay_c;
        $template = ob_get_clean();
        
        // controller
        ob_start();
        include $_tmp_c;
        $ctrl = ob_get_clean();
        
        $template = str_replace("{content}", $ctrl, $template);
        preg_match_all('/{module name=\"([a-zA-Z][a-zA-Z0-9]*)\" parameters="([^\{\}]*)"}/s', $template, $modules, PREG_SET_ORDER);
        foreach($modules as $i) {
            $result = "";
            $url = dirname(dirname(__DIR__))."/modules/".$i[1]."/";
            if(file_exists($url."/Module.php")) {
                include $url."/Module.php";
                $className = ucfirst($i[1]);
                $module = new $className();
                $data = $module->getData(($i[2]==="") ? null : json_decode("{".str_replace(["[", "]", "'"], ["{", "}", "\""], $i[2])."}", TRUE));
                $tmpname = $module->getTmp();
                $compurl = dirname(dirname(__DIR__))."/compilation/".md5("modules".$i[1].$tmpname)."_modules_".$i[1]."_".$tmpname.".php";
                if(!file_exists($compurl) || filemtime($url."/".$tmpname.".html") >= filemtime($compurl)) {
                    $_tmp = $this->compile(\file_get_contents($url."/".$tmpname.".html"), $url."/".$tmpname.".html");
                    \file_put_contents($compurl, $_tmp);
                }
                $result = $this->includeModule($compurl, $data);
            }
            $template = str_replace($i[0], $result, $template);
        }        
        
        echo $template;
    }
    
    private function includeRec($template) {
        preg_match_all('/{include name=\"([a-zA-Z][a-zA-Z0-9]*)\"}/s', $template, $includes, PREG_SET_ORDER);
        foreach($includes as $i) {
            // include_file
            $_inc = dirname(dirname(__DIR__))."/design/".$this->settings["type"]."/includes/" . $i[1] . ".html";
            $_inc_c = dirname(dirname(__DIR__)) . "/compilation/" . md5($this->settings["type"]."includes".$i[1]) . "_" . $this->settings["type"] . "_includes_" . $i[1] . ".php";
            if(!file_exists($_inc_c) || filemtime($_inc) >= filemtime($_inc_c)) {
                $tmp = $this->compile(\file_get_contents($_inc), $_inc);
                \file_put_contents($_inc_c, $tmp);
            }
            $template = str_replace($i[0], '<?php include dirname(__DIR__)."/compilation/".md5("'.$this->settings["type"]."includes".$i[1].'") . "_'.$this->settings["type"].'_includes_'.$i[1].'.php"; ?>', $template);
        }
        return $template;
    }
    
    private function includeModule($url, $data) {
        if(!is_null($data)) extract($data);
        ob_start();
        include $url;
        return ob_get_clean();
    }
    
    private function compile($template, $tmp) {      
        // ajax
        preg_match_all('/{ajax name=\"([a-zA-Z][a-zA-Z0-9]*)\"}(.*?){\/ajax}/s', $template, $ajax, PREG_SET_ORDER);
        foreach($ajax as $val) {
            $file = dirname($tmp).'/ajax/'.$val[1].'.html';
            if (!file_exists(dirname($file))) {
               mkdir(dirname($file), 0777, true);
            }
            file_put_contents($file, $val[2]);
            $template = str_replace($val[0], $val[2], $template);
        }
        
        $template = preg_replace('/{\/ajax}/Us', '<?php } ?>', $template); 
        
        $template = $this->includeRec($template);
        // variables
        $template = preg_replace('/{\$([a-z-A-Z0-9][a-zA-Z0-9]*) = (0-9\.)}/Us', '<?php $$1 = \'$2\'; ?>', $template); 
        $template = preg_replace('/{\$([a-z-A-Z0-9][a-zA-Z0-9]*) = ([^\}\{]+)}/Us', '<?php $$1 = \'$2\'; ?>', $template);
        $template = preg_replace('/{\$([^\{\}]+)}/Us', '<?php echo $$1; ?>', $template);
        $template = preg_replace('/{echo ([^\{\}]+)}/Us', '<?php echo $1; ?>', $template);
        // if
        $template = preg_replace('/{if ([^\}\{]+)}/Us', '<?php if($1) { ?>', $template); 
        $template = preg_replace('/{\/if}/Us', '<?php } ?>', $template); 
        // foreach
        $template = preg_replace('/{foreach \$([a-zA-Z][a-zA-Z0-9\_]*(\[(\"|\'|)[a-zA-Z0-9]+(\"|\'|)\])*) as \$([a-zA-Z][a-zA-Z0-9]*)}/Us', '<?php foreach($$1 as $$5) { ?>', $template);
        $template = preg_replace('/{foreach \$([a-zA-Z][a-zA-Z0-9\_]*(\[(\"|\'|)[a-zA-Z0-9]+(\"|\'|)\])*) as \$([a-zA-Z][a-zA-Z0-9]*)[ ]*=>[ ]*\$([a-zA-Z][a-zA-Z0-9]*)}/Us', '<?php foreach($$1 as $$5 => $$6) { ?>', $template);
        $template = preg_replace('/{\/foreach}/Us', '<?php } ?>', $template);
        return $template;
    }
    
}