<?php

$routes = array();
$routes['registro'] = 'usuario/registro';
$routes['perfil'] = 'usuario/verUsuario';
$routes['cerrarsesion'] = '/login/cerrar';
$routes['pruebas/(:any)/(:any)'] = 'usuario/test/$1/$2';


define("ROUTES", json_encode($routes));