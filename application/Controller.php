<?php

abstract class Controller {

    protected $_view;

    public function __construct() {
        $this->_view = new View(Router::getInstance());
    }

    abstract public function index();

    protected function loadModel($modelo) {
        $modelo = $modelo . 'Model';
        $rutaModelo = ROOT . 'models' . DS . $modelo . '.php';

        if (is_readable($rutaModelo)) {
            require_once $rutaModelo;
            $modelo = new $modelo;
            return $modelo;
        } else {
            throw new Exception('Error de modelo');
        }
    }

    /* Carga la librerias especificada. */

    protected function getLibrary($libreria) {
        $rutaLibreria = ROOT . 'libs' . DS . $libreria . '.php';

        if (is_readable($rutaLibreria)) {
            require_once $rutaLibreria;
        } else {
            throw new Exception('Error de libreria');
        }
    }

    /* Convierte caracteres especiales en texto. */

    protected function getTexto($clave) {
        if (isset($_POST[$clave]) && !empty($_POST[$clave])) {
            $_POST[$clave] = htmlspecialchars($_POST[$clave], ENT_QUOTES);
            return $_POST[$clave];
        }

        return '';
    }

    /* Obtiene un numero del parametro $_POST. */

    protected function getInt($clave) {
        if (isset($_POST[$clave]) && !empty($_POST[$clave])) {
            $_POST[$clave] = filter_input(INPUT_POST, $clave, FILTER_VALIDATE_INT);
            return $_POST[$clave];
        }

        return 0;
    }

    protected function getSql($clave) {
        if (isset($_POST[$clave]) && !empty($_POST[$clave])) {
            $_POST[$clave] = strip_tags($_POST[$clave]);

            if (!get_magic_quotes_gpc()) {
                $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
                $_POST[$clave] = mysqli_real_escape_string($con, $_POST[$clave]);
            }
            return trim($_POST[$clave]);
        }
    }

    /* Redirecciona a la ruta especificada. */

    protected function redireccionar($ruta = FALSE) {
        if ($ruta) {
            header('location:' . BASE_URL . $ruta);
            exit();
        } else {
            header('location:' . BASE_URL);
            exit();
        }
    }

    /*
     * Recibe un string e ingenta castearlo a un intiger para devolverlo.
     * Caso contrario retorna 0. 
     */

    protected function filtrarInt($int) {
        $int = (int) $int;

        if (is_int($int)) {
            return $int;
        } else {
            return 0;
        }
    }

    protected function getPostParam($clave) {
        if (isset($_POST[$clave])) {
            return $_POST[$clave];
        }
    }

    protected function getAlphaNum($clave) {
        if (isset($_POST[$clave]) && !empty($_POST[$clave])) {
            $_POST[$clave] = (string) preg_replace('/[^A-Z0-9_]/i', '', $_POST[$clave]);
            return trim($_POST[$clave]);
        }
    }

    public function validarEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return FALSE;
        }

        return true;
    }

}

?>