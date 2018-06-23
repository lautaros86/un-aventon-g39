<?php

define('DEFAULT_CONTROLLER', 'index');
define('DEFAULT_METHOD', 'index');
define('DEFAULT_LAYOUT', 'default');

define('APP_NAME', 'UnAventon');
define('APP_SLOGAN', 'Un Aventon');
define('APP_COMPANY', 'www.lsilva.com.ar');
define('SESSION_TIME', 1);
define('HASH_KEY', '53bf025929f1f');
define('ENV_DEV', true);

if ($_SERVER['HTTP_HOST'] == 'unaventon.local') {
    define('BASE_URL', 'http://unaventon.local/');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'grupo_39');
    define('DB_CHAR', 'utf8');
}
if ($_SERVER['HTTP_HOST'] == 'unaventon.local:8080') {
    define('BASE_URL', 'http://unaventon.local:8080/');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'grupo_39');
    define('DB_CHAR', 'utf8');
}

if ($_SERVER['HTTP_HOST'] == 'unaventon.lsilva.com.ar') {
    define('BASE_URL', 'http://unaventon.lsilva.com.ar/');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'lsilvaco_g39');
    define('DB_PASS', '1Q2w3e4r');
    define('DB_NAME', 'lsilvaco_grupo39');
    define('DB_CHAR', 'utf8');
    define('ENV_DEV', false);
}

if ($_SERVER['HTTP_HOST'] == '35.197.185.188 ') {
    define('BASE_URL', 'http://35.197.185.188 /');
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '1234');
    define('DB_NAME', 'grupo_93');
    define('DB_CHAR', 'utf8');
    define('ENV_DEV', false);
}
?>
