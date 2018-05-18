<?php

ini_set('display_errors', 1);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(__FILE__)) . DS);
define('APP_PATH', ROOT . 'application' . DS);
define('CNT_PATH', ROOT . 'controllers' . DS);
define('MOD_PATH', ROOT . 'models' . DS);
define('VIE_PATH', ROOT . 'views' . DS);
define('VEN_PATH', ROOT . 'vendor' . DS);

try {
    require_once APP_PATH . 'Config.php';
    require_once APP_PATH . 'Request.php';
    require_once APP_PATH . 'Bootstrap.php';
    require_once APP_PATH . 'Controller.php';
    require_once APP_PATH . 'Model.php';
    require_once APP_PATH . 'View.php';
    require_once APP_PATH . 'Registro.php';
    require_once APP_PATH . 'Database.php';
    require_once APP_PATH . 'Session.php';
    require_once APP_PATH . 'Hash.php';
    require_once APP_PATH . 'Router.php';
    require_once APP_PATH . 'Routes.php';
    require_once VEN_PATH . 'autoload.php';

    Session::init();

    Bootstrap::run(Router::getInstance());
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
