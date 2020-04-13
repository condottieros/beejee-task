<?php
session_start();
include '../src/Controller.php';
$routes_config = require '../src/routes_config.php';
$controller = new Controller($routes_config);
echo $controller->handle();


/*
echo $_SERVER['REQUEST_URI'];
echo '<br>--->'.$_GET['_url'];
echo 'method--> '.$_SERVER['REQUEST_METHOD'];
*/

