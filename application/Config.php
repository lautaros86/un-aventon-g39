<?php

define('DEFAULT_CONTROLLER', 'index');
define('DEFAULT_METHOD', 'index');
define('DEFAULT_LAYOUT', 'default');

define('APP_NAME', 'Mi Framwork');
define('APP_SLOGAN', 'Mi primer Framework php y mvc.....');
define('APP_COMPANY', 'www.jcbcsystems.com');
define('SESSION_TIME', 1);
define('HASH_KEY', '53bf025929f1f');

if ($_SERVER['HTTP_HOST'] == 'unaventon.local') {
    define('BASE_URL', 'http://unaventon.local/');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'grupo_39');
    define('DB_CHAR', 'utf8');
}

if ($_SERVER['HTTP_HOST'] == 'grupo39.lsilva.com.ar') {
    define('BASE_URL', 'http://grupo39.lsilva.com.ar/');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'lsilvaco_g39');
    define('DB_PASS', '1Q2w3e4r');
    define('DB_NAME', 'lsilvaco_grupo39');
    define('DB_CHAR', 'utf8');
}
?>
