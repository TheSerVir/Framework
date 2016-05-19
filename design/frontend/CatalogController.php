<?php
class CatalogController extends ksf\Controller {
    public static function getLinks() {
        return [
                "/{s:category}/{i:page}-{i:to}" => "category",
                "/{s:category}/{i:page}" => "category",
            
                "/{s:category}+{s:brands}/{i:page}-{i:to}" => "category",
                "/{s:category}+{s:brands}/{i:page}" => "category",
            
                "/ajax" => "ajax",
            
                "/{s:parcategory}/{s:category}+{s:brands}/{i:page}-{i:to}" => "category",
                "/{s:parcategory}/{s:category}+{s:brands}/{i:page}" => "category",
                "/{s:parcategory}/{s:category}+{s:brands}/{f:filter}/{i:page}-{i:to}" => "category",
                "/{s:parcategory}/{s:category}+{s:brands}/{f:filter}/{i:page}" => "category",
                "/{s:parcategory}/{s:category}+{s:brands}/{f:filter}" => "category",
                "/{s:category}+{s:brands}/{f:filter}/{i:page}-{i:to}" => "category",
                "/{s:category}+{s:brands}/{f:filter}/{i:page}" => "category",
                "/{s:category}+{s:brands}/{f:filter}" => "category",

                "/{s:parcategory}/{s:category}/{i:page}-{i:to}" => "category",
                "/{s:parcategory}/{s:category}/{i:page}" => "category",
                "/{s:parcategory}/{s:category}/{f:filter}/{i:page}-{i:to}" => "category",
                "/{s:parcategory}/{s:category}/{f:filter}/{i:page}" => "category",
                "/{s:parcategory}/{s:category}/{f:filter}" => "category",
                "/{s:category}/{f:filter}/{i:page}-{i:to}" => "category",
                "/{s:category}/{f:filter}/{i:page}" => "category",
                "/{s:category}/{f:filter}" => "category",
            
                "/{s:category}/{s:product_category}" => 
                ["SELECT COUNT(*) AS `cnt` FROM `categories` WHERE `link` = ? AND `parent` IN (SELECT `id` FROM `categories` WHERE `link` = ?)",
                    [function($res) {
                        return $res["cnt"] == 1;
                    },
                ["product_category", "category"] ],
                                                       ["/{s:parcategory}/{s:category}", "category"], ["/{s:category}/{s:product}", "product"]],
                        
                "/{s:category}" => "category",
                "/" => "index"                     
            ];
    }
    
    public function indexAction() {
        echo "index";
        $this->i = ["C", "Y", "C", "L", "E"];
    }
    
    public function categoryAction() {
        echo "category";
    }
    
    public function productAction() {
        echo "product";
    }
    
    function page404Action() {
        echo "page404";
    }
}