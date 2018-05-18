<?php

$routes = array();
$routes['registro'] = 'usuario/registro';
$routes['pruebas/(:any)/(:any)'] = 'usuario/test/$1/$2';


define("ROUTES", json_encode($routes));