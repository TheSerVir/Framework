<?php

class PagesController extends ksf\Controller {

    public static function getLinks() {
        return [
                "/" => "reviews"        
            ];
    }
    
    public function reviewsAction() {
        echo "reviews";
    }

}