<?php
/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    "driver" => "mysql",
    "host" => \App\Config::DB_HOST,
    "database" => \App\Config::DB_NAME,
    "username" => \App\Config::DB_USER,
    "password" => \App\Config::DB_PASSWORD
]);

//Make this Capsule instance available globally.
$capsule->setAsGlobal();
// Setup the Eloquent ORM.
$capsule->bootEloquent();

session_start();

/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Routing
 */
$router = new Core\Router();


// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index', 'auth' => false]);
$router->add('sign-in', ['controller' => 'Auth', 'action' => 'login', 'auth' => false]);
$router->add('login',   ['controller' => 'Auth', 'action' => 'loginPost', 'auth' => false]);
$router->add('sign-up', ['controller' => 'Auth', 'action' => 'register', 'auth' => false]);
$router->add('register', ['controller' => 'Auth', 'action' => 'registerPost', 'auth' => false]);
$router->add('logout', ['controller' => 'Auth', 'action' => 'logout', 'auth' => true]);

$router->add('home', ['controller' => 'Home', 'action' => 'welcome', 'auth' => true]);
$router->add('chat', ['controller' => 'Chat', 'action' => 'index', 'auth' => true]);
    
$router->dispatch($_SERVER['QUERY_STRING']);
