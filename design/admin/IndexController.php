<?php
class IndexController extends ksf\Controller {
    public static function getLinks() {
        return [
                "/{s:category}/{s:item}" => "product",
                "/{s:category}" => "items",                  
            ];
    }
    
    public function itemsAction() {
        echo "itemsAdmin";
    }
    
    public function productAction() {
        echo "productAdmin";
    }
    
    function page404Action() {
        echo "page404";
    }
}