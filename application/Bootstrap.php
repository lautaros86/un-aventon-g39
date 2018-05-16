<?php

class Bootstrap {

    public static function run(Request $peticion) {
        $controller = $peticion->getControlador() . 'Controller';
        $rutaControlador = ROOT . 'controllers' . DS . $controller . '.php';
        $metodo = $peticion->getMetodo();
        $args = $peticion->getArgs();

        if (is_readable($rutaControlador)) {
            require_once $rutaControlador;
            $controller = new $controller;

            if (is_callable(array($controller, $metodo))) {
                $metodo = $peticion->getMetodo();
            } else {
                $metodo = 'index';
            }

            if (isset($args) && sizeof($args) > 0) {
                call_user_func_array(array($controller, $metodo), $args);
            } else {
                call_user_func(array($controller, $metodo));
            }
        } else {
            require_once ROOT . "controllers/notFoundController.php";
            call_user_func(array(new notFoundController(), "index"));
        }
    }

}

?>
