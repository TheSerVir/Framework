<?php
class AdminController extends ksf\Controller {
    
    public static function getLinks() {
        return [
                "/" => "reviews"
            ];
    }
    
    public function reviewsAction() {
        echo "reviewsAdmin";
    }

}