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
$router->addRoute('profil','ProfileController@index');
$router->addRoute('register/submit', 'AuthController@handleRegister');
$router->addRoute('login/submit', 'AuthController@handleLogin');
$router->addRoute('chapter/{id}', 'ChapterController@show');
$router->addRoute('character/create', 'CharacterController@index');
$router->addRoute('character/handleHero', 'CharacterController@handleHero');
$router->addRoute('character/list', 'CharacterController@list');

$router->route(trim($_SERVER['REQUEST_URI'], '/'));