<?php

abstract class Controller {

    protected $_view;
    protected $_notificacion;
    private $_registro;
    private $_usuario;
    private $_viajes;
    private $_tarjeta;
    private $_facturas;
    private $_calificacion;
    private $_wallet;
    private $_vehiculo;

    public function __construct() {
        $this->_view = new View(Router::getInstance());
        require_once ROOT . 'controllers' . DS . 'notificacionController.php';
        $this->_notificacion = new notificacionController();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'viajeModel.php';
        require_once ROOT . 'models' . DS . 'tarjetaModel.php';
        require_once ROOT . 'models' . DS . 'facturaModel.php';
        require_once ROOT . 'models' . DS . 'calificacionModel.php';
        require_once ROOT . 'models' . DS . 'walletModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $this->_registro = new registroModel();
        $this->_usuario = new usuarioModel();
        $this->_viajes = new viajeModel();
        $this->_tarjeta = new tarjetaModel();
        $this->_facturas = new facturaModel();
        $this->_calificacion = new calificacionModel();
        $this->_wallet = new walletModel();
        $this->_vehiculo = new vehiculoModel();
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

    protected function cargardatos() {
        $params = array();
        $params["usuario"] = $this->_usuario->getUsuario(Session::get("id_usuario"));
        $params["vehiculos"] = $this->_vehiculo->getVehiculosActivosByUserId($params["usuario"]['id']);
        $params["vehiculosInactivos"] = $this->_vehiculo->getVehiculosInactivosByUserId($params["usuario"]['id']);
        $params["viajes"] = $this->_viajes->getViajesDe(Session::get("id_usuario"));
        $params["viajesPostulados"] = $this->_viajes->getViajesPostuladosOf(Session::get("id_usuario"));
        $params["viajesAbiertos"] = $this->_viajes->getViajesAbiertosDe(Session::get("id_usuario"));
        $params["viajesIniciados"] = $this->_viajes->getViajesIniciadosDe(Session::get("id_usuario"));
        $params["viajesCancelados"] = $this->_viajes->getViajesCanceladosDe(Session::get("id_usuario"));
        $params["viajesFinalizados"] = $this->_viajes->getViajesFinalizadosDe(Session::get("id_usuario"));
        $params["tarjetas"] = $this->_tarjeta->getTarjetasDeUnUsuario($params["usuario"]['id']);
        $params["usuario"]['cantViajesChofer'] = $this->_viajes->getCantViajesChofer($params["usuario"]['id']);
        $params["usuario"]['cantViajesPasajero'] = $this->_viajes->getCantViajesPasajero($params["usuario"]['id']);
        $params["misNotificaciones"] = $this->_notificacion->getNotificacionesOf($params["usuario"]["id"]);
        $params["facturas"] = $this->_facturas->getFacturasOf($params["usuario"]["id"]);
        $params["calificaciones"] = $this->_usuario->getCalificacionesOf($params["usuario"]["id"]);
        $params["misCalificaciones"] = $this->_calificacion->getAllCalificacionesRecibidas($params["usuario"]["id"]);
        $params["puedePublicarPostular"] = $this->_usuario->calcularPuedePublicarPostular($params["usuario"]["id"]);
        $params["verificarEliminar"] = $this->_usuario->verificarEliminar($params["usuario"]["id"]);
        $params["saldoWallet"] = $this->_wallet->getSaldo($params["usuario"]["id"]);
        return $params;
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

    protected function verifInt($clave) {
        if (isset($_POST[$clave]) && !empty($_POST[$clave])) {
            $_POST[$clave] = filter_input(INPUT_POST, $clave, FILTER_VALIDATE_INT);
            return $_POST[$clave];
        }
        return 0;
    }

    protected function verifRequire($clave) {
        if (isset($_POST[$clave]) && !empty($_POST[$clave])) {
            $_POST[$clave] = strip_tags($_POST[$clave]);
            if (!get_magic_quotes_gpc()) {
                $con = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
                $_POST[$clave] = mysqli_real_escape_string($con, $_POST[$clave]);
            }
            return trim($_POST[$clave]);
        }
        return false;
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