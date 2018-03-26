<?php

if ($_SERVER['HTTP_HOST'] == 'unaventon.local') {
    define('BASE_URL', 'http://unaventon.local/');
}
if ($_SERVER['HTTP_HOST'] == 'unaventon.com.ar') {
    define('BASE_URL', 'http://unaventon.com.ar/');
}
define('DEFAULT_CONTROLLER', 'index');
define('DEFAULT_LAYOUT', 'default');


define('APP_NAME', 'Mi Framwork');
define('APP_SLOGAN', 'Mi primer Framework php y mvc.....');
define('APP_COMPANY', 'www.jcbcsystems.com');
define('SESSION_TIME', 1);
define('HASH_KEY', '53bf025929f1f');
if ($_SERVER['HTTP_HOST'] == 'unaventon.local') {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'blog');
    define('DB_CHAR', 'utf8');
}

if ($_SERVER['HTTP_HOST'] == 'unaventon.com.ar') {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'aventon');
    define('DB_CHAR', 'utf8');
}
?>
