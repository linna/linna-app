<?php

/**
 * Linna App
 *
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2016, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */

use Linna\Database\MysqlPDOAdapter;
use Linna\Database\Database;
use Linna\Session\Session;
use Linna\Http\Router;
use Linna\Http\FrontController;
use Linna\DI\DIResolver;
use Linna\Autoloader;

//load configuration from config file
require '../App/config/config.php';

//load routes.
require APP . '/config/routes.php';

//composer autoload
require ROOT . '/vendor/autoload.php';


//linna autoloader, load application class
//for more information see http://www.php-fig.org/psr/psr-4/
$loader = new Autoloader();
$loader->register();

$loader->addNamespaces([
    ['App\Models', __DIR__ . '/../App/Models'],
    ['App\Views', __DIR__ . '/../App/Views'],
    ['App\Controllers', __DIR__ . '/../App/Controllers'],
    ['App\Templates', __DIR__ . '/../App/Templates'],
    ['App\Mappers', __DIR__ . '/../App/Mappers'],
    ['App\DomainObjects', __DIR__ . '/../App/DomainObjects'],
]);

//create adapter
$MysqlAdapter = new MysqlPDOAdapter(
        DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET,
        DB_USER,
        DB_PASS,
        array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING)
        );

//create data base object
$DataBase = new Database($MysqlAdapter);

//create dipendency injection resolver
$DIResolver = new DIResolver();

//add unresolvable class to DIResolver
$DIResolver->cacheUnResolvable('\Linna\Database\Database', $DataBase);

$sessionHandler = $DIResolver->resolve('\Linna\Session\DatabaseSessionHandler');


//set session handler
//optional if not set, app will use php session standard storage
Session::setSessionHandler($sessionHandler);

//se session options
Session::withOptions(array(
    'expire' => 1800,
    'cookieDomain' => URL_DOMAIN,
    'cookiePath' => URL_SUB_FOLDER,
    'cookieSecure' => false,
    'cookieHttpOnly' => true
));

//store session instance
//call getInstance start the session
$DIResolver->cacheUnResolvable('\Linna\Session\Session', Session::getInstance());

//start router
$router = new Router($appRoutes, array(
    'basePath' => URL_SUB_FOLDER,
    'badRoute' => 'E404',
    'rewriteMode' => REWRITE_ENGINE
        ));

//evaluate request uri
$router->validate($_SERVER['REQUEST_URI']);

//get route
$route = $router->getRoute();

//get model linked to route
$routeModel = '\App\Models\\'.$route->getModel();
//get view linked to route
$routeView = '\App\Views\\'.$route->getView();
//get controller linked to route
$routeController = '\App\Controllers\\'.$route->getController();
    
//resolve model
$model = $DIResolver->resolve($routeModel);

//resolve view
$view = $DIResolver->resolve($routeView);

//resolve controller
$controller = $DIResolver->resolve($routeController);


//start front controller
$frontController = new FrontController($route, $model, $view, $controller);

//run
$frontController->run();

//get front controller response
$frontController->response();

//only for debug, return time execution and memory usage
//echo '<!-- Memory: ';
//echo round(xdebug_memory_usage() / 1024, 2) , ' (';
//echo round(xdebug_peak_memory_usage() / 1024, 2) , ') KByte - Time: ';
//echo xdebug_time_index();
//echo ' Seconds -->';
