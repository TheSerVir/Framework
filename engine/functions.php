<?php
function getConfigs() {
    $file = __DIR__."/configs/configs.ini";
    $file_dat = "$file.dat";
    if (!file_exists($file_dat) || filemtime($file) >= filemtime($file_dat)) {
        $r = parse_ini_file($file);
        if ($F = fopen($file_dat, "w")) {
            fwrite($F, serialize($r));
            fclose($F);
        }
    } else {
        $r = unserialize(file_get_contents($file_dat));
    }

    return $r;   
}

function getControllers() {
    $dir = opendir(dirname(__FILE__)."/controllers/");
    $result = array();
    while(false !== ($file = readdir($dir))) {
        if(strlen($file) > 3 && substr($file, -3) == "php") 
            $result[] = str_replace(".php", "", $file);
    }
    return $result;
}

function translit($text) {
    $rus = array("а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я", " ", "/", "\\", ".", ",", ":", "і", "+", "!");
    $eng = array("a", "b", "v", "g", "d", "e", "e", "zh", "z", "i", "y", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "kh", "ts", "ch", "sh", "shch", "", "y", "", "e", "yu", "ya", "_", "", "", "", "", "", "i", "plus", "");
    return str_replace($rus, $eng, mb_strtolower($text, 'UTF-8'));
}

function getProductCount($number) {
    $number = $number % 100;
    if ($number>=11 && $number<=19) {
        return "товаров";
    }
    else {
        $i = $number % 10;
        switch ($i)
        {
            case (1): $ending = "товар"; break;
            case (2): 
            case (3): 
            case (4): $ending = "товара"; break;
            default: $ending = "товаров"; break;
        }
    }
    return $ending;
}

function russian_date($date1, $delimiter = ".", $reverse = false){
	$date=explode($delimiter, $date1);
	switch ($date[1]){
	case 1: $m='января'; break;
	case 2: $m='февраля'; break;
	case 3: $m='марта'; break;
	case 4: $m='апреля'; break;
	case 5: $m='мая'; break;
	case 6: $m='июня'; break;
	case 7: $m='июля'; break;
	case 8: $m='августа'; break;
	case 9: $m='сентября'; break;
	case 10: $m='октября'; break;
	case 11: $m='ноября'; break;
	case 12: $m='декабря'; break;
	}
	$v1 = 2;
	$v2 = 0;
	if($reverse) list($v1, $v2) = array(0, 2);
	if($date[2] == date("Y"))
		return $date[$v2].'&nbsp;'.$m;
	else
		return $date[$v2].'&nbsp;'.$m.'&nbsp;'.$date[$v1];
}

function resize($p_url, $g_height = null, $g_width = null) {
    if(!is_null($p_url)) {
        $path = dirname(dirname(dirname(__FILE__)).$p_url);
        $url = basename($p_url);
        if(!file_exists(dirname(dirname(__FILE__)).$p_url)) return $p_url;
        $pathFrom = $path."/".$url;

        if(!is_null($g_height) || !is_null($g_width)) {
            $height = (!is_null($g_height)) ? $g_height : 0;
            $width = (!is_null($g_width)) ? $g_width : 0;
        } else {
            return $p_url;
        }

        $pathTo = dirname(dirname(__FILE__))."/uploads/images/thumbnail-".$height."x".$width."-".$url;

        if(!file_exists($pathTo)) {
            $image = new SimpleImage();
            $image->load($pathFrom);
            if($width != 0) $image->resizeToWidth($width); // В аргумент ширину картинки, которая нужна (Она пропорц. уменьш.)
            if($height != 0) $image->resizeToHeight($height); // В аргумент ширину картинки, которая нужна (Она пропорц. уменьш.)
            $image->save($pathTo); // Сохраняем
            unset($image);
        }
        
        return "/uploads/images/thumbnail-".$height."x".$width."-".$url;
    }   
}

function setVariable($name, $value) {
    global $variables;
    $variables->$name = $value;
}

function errorHandler($errno, $errstr, $errfile, $errline) {
    global $configs;
    switch($configs["ErrMode"]) {
        case 2: // отправлять на мыло
            mail ($configs->ErrMail, "Site Error", "Ошибка на сайте: ".$_SERVER["SERVER_NAME"].
                                                   "\n Ошибка:".$errno." ".$errstr.
                                                   "\n Строка #".$errline.
                                                   "\n Имя файла: ".$errfile.
                                                   "\n".implode("\n", debug_backtrace()));
            echo Language::getPhrase(2);
            exit;
        break;
        case 3: // не показывать
            echo Language::getPhrase(2);
            exit;
        break;
        case 4: // не показывать
            echo "<div style=\"width:100%; margin:20px; font-style:italic; font-size:20pt;\">\n
                    <p style=\"margin:10px;\"><b>Ошибка:</b> ".$errstr."</p> \n
                  </div>";
            exit;
        break;
        default: // тогда mode = 1
            echo "
                <div style=\"width:100%; margin:20px;\">\n
                    <p style=\"margin:10px;\">Ошибка: ".$errstr."</p> \n
                    <p style=\"margin:10px;\">Номер ошибки: ".$errno."</p> \n
                    <p style=\"margin:10px;\">Файл: ".$errfile."</p> \n
                    <p style=\"margin:10px;\">Строка: ".$errline."</p> \n
                    <pre style=\"margin:10px;\">";
                debug_print_backtrace();
                echo "</pre>
                </div>";
            exit;
        break;
    }
}

set_error_handler('errorHandler');
?>