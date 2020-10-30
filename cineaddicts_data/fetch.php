<?php 

//  Display all errors during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

//  Increase memory limit
ini_set('memory_limit','1024M');

//  Sets the default timezone used by all date/time functions
date_default_timezone_set('America/Montreal');

//  Autoloader
spl_autoload_register(function ($className) {
    require_once($className.'.php');
});

$fetcherObj = new WikiDataFetcher();
//$fetcherObj->fillRoleTables();
$fetcherObj->fetch('all_movies.csv');

?>