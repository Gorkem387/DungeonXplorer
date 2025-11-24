<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'autoload.php';
require 'core/Router.php';

$router = new Router('');

$router->addRoute('', 'HomeController@index');
$router->addRoute('testdb', 'TestController@db');
$router->addRoute('login', 'AuthController@showLogin');
$router->addRoute('register', 'AuthController@showRegister');
$router->addRoute('register/submit', 'AuthController@handleRegister');
$router->addRoute('login/submit', 'AuthController@handleLogin');

$router->route(trim($_SERVER['REQUEST_URI'], '/'));