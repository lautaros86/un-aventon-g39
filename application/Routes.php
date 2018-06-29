<?php

$routes = array();
$routes['registro'] = 'usuario/registro';
$routes['perfil'] = 'usuario/verusuario';
$routes['cerrarsesion'] = '/login/cerrar';
$routes['preguntas-frecuentes'] = '/paginaestatica/preguntasFrecuentes';
$routes['pruebas/(:any)/(:any)'] = 'usuario/test/$1/$2';


define("ROUTES", json_encode($routes));