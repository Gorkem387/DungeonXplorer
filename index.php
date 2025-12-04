<?php
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'autoload.php';
require 'core/Router.php';

$router = new Router('');

$router->addRoute('', 'HomeController@index');
$router->addRoute('testdb', 'TestController@db');
$router->addRoute('login', 'AuthController@showLogin');
$router->addRoute('logout', 'AuthController@showLogout');
$router->addRoute('register', 'AuthController@showRegister');
$router->addRoute('profil','ProfileController@index');
$router->addRoute('register/submit', 'AuthController@handleRegister');
$router->addRoute('login/submit', 'AuthController@handleLogin');
$router->addRoute('chapter/{id}', 'ChapterController@show');
$router->addRoute('chapitre/next', 'ChapterController@handleNext');
$router->addRoute('chapitre/start', 'ChapterController@Start');
$router->addRoute('hero', 'CharacterController@index');
$router->addRoute('hero/submit', 'CharacterController@handleHero');
$router->addRoute('admin', 'AdminController@index');
$router->addRoute('admin/joueur', 'AdminController@listeJoueur');
$router->addRoute('admin/delete', 'AdminController@deleteJoueur');
$router->addRoute('admin/chapter', 'AdminController@chapter');
$router->addRoute('admin/chapter/add', 'AdminController@chapterAddPage');
$router->addRoute('admin/chapter/delete', 'AdminController@chapterDelete');
$router->addRoute('admin/chapter/modify', 'AdminController@chapterModifyPage');
$router->addRoute('admin/chapter/modify/modify', 'AdminController@chapterModify');
$router->addRoute('admin/chapter/add/add', 'AdminController@chapterAdd');
$router->addRoute('character/list', 'CharacterController@list');
//combat
$router->addRoute('combat/start/{id}', 'CombatController@start');
$router->addRoute('combat/fight', 'CombatController@fight');
$router->addRoute('combat/attack', 'CombatController@attack');
$router->addRoute('combat/magic', 'CombatController@magic');
$router->addRoute('combat/defend', 'CombatController@defend');
$router->addRoute('combat/end', 'CombatController@end');
$router->addRoute('combat/action', 'CombatController@handleAction');
$router->addRoute('combat/inventory', 'CombatController@getInventory');



$router->route(trim($_SERVER['REQUEST_URI'], '/'));