<?php

namespace controller;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use daos\associationDaos;

class homeController
{
    public function __construct() {
        $this->header();
        $this->menu();
    }
    function header(){
        $associationsId = 0;
        $associations = associationDaos::getAllAssoc($associationsId);
        include __DIR__ .'/../view/header.php'; 
    }
    function menu(){
        include __DIR__ .'/../view/menu.php'; 
    }
}

