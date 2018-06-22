<?php

class loginController extends Controller {

    private $_login;
    private $_vehiculo;
    private $_factura;

    public function __construct() {
        parent::__construct();
        $this->_login = $this->loadModel('login');
        $this->_vehiculo = $this->loadModel('vehiculo');
        require_once ROOT . 'models' . DS . 'facturaModel.php';
        $this->_factura = new facturaModel();
    }

    public function index() {
        if (Session::get('autenticado')) {
            $this->redireccionar();
        }
        $this->_view->renderizar('index', 'login');
    }

    public function ingresar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("login");
        }

        if (!$this->getPostParam('email')) {
            Session::setMessage('Debe introducir su mail de usuario', SessionMessageType::Error);
            $this->_view->renderizar('index', 'login');
            exit;
        }

        if (!$this->getPostParam('pass')) {
            Session::setMessage('Debe introducir su password', SessionMessageType::Error);
            $this->_view->renderizar('index', 'login');
            exit;
        }

        $usuario = $this->_login->getUsuario(
                $this->getPostParam('email'), $this->getPostParam('pass')
        );

        if (!$usuario) {
            Session::setMessage('Usuario y/o password incorrectos', SessionMessageType::Error);
            $this->redireccionar('login');
            exit;
        }

        if ($usuario['estado'] == 2) {
            Session::setMessage('Este usuario no esta habilitado', SessionMessageType::Error);
            $this->redireccionar('login');
            exit;
        }

        $cant = $this->_vehiculo->cantVehiculos($usuario['id']);
        if ($cant > 0) {
            Session::set('chofer', true);
        } else {
            Session::set('chofer', false);
        }

        Session::set('esDeudor', $this->esDeudor($usuario));
        Session::set('autenticado', true);
        Session::set('usuario', $usuario);
        Session::set('id_usuario', $usuario['id']);
        $this->redireccionar('usuario/verUsuario');
    }

    public function cerrar() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        Session::destroy();
        $this->redireccionar();
    }

    public function esDeudor($usuario) {
        $pendientes = $this->_factura->getFacturasPendinetesOf($usuario["id"]);
        return sizeof($pendientes) > 0;
    }

}

?>