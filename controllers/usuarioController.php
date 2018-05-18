<?php

class usuarioController extends Controller {

    private $_registro;

    public function __construct() {
        parent::__construct();
        $this->_registro = $this->loadModel('registro');
    }

    public function index() {
        
    }

    public function registro() {
        if (Session::get('autenticado')) {
            $this->redireccionar();
        }
        $this->_view->renderizar('registro', 'usuario');
    }

    public function crear() {
        if (Session::get('autenticado')) {
            $this->redireccionar();
        }

        $data = $_POST;
        if (!$this->getSql('nombre')) {
            $this->_view->_error = "Debe introducir su nombre";
            exit;
        }

        if ($this->_registro->verificarUsuario($this->getAlphaNum('nombre'))) {
            $this->_view->_error = "El usuario " . $this->getAlphaNum('nombre') . " ya existe";
            exit;
        }

        if ($this->_registro->verificarUsuario($this->getAlphaNum('apellido'))) {
            $this->_view->_error = "El usuario " . $this->getAlphaNum('apellido') . " ya existe";
            exit;
        }

        if (!$this->validarEmail($this->getPostParam('email'))) {
            $this->_view->_error = "La direccion de email es inv&aacute;ida";
            exit;
        }

        if ($this->_registro->verificarEmail($this->getPostParam('email'))) {
            $this->_view->_error = "Esta direccion de correo ya esta registrada";
            exit;
        }

        if (!$this->getSql('pass')) {
            $this->_view->_error = "Debe introducir un password";
            exit;
        }

        if ($this->getPostParam('pass') != $this->getPostParam('confirmar')) {
            $this->_view->_error = "Los passwords no coinciden";
            exit;
        }

        $this->_view->setMessage(array("type"=>"danger", "message"=>"Esto es un mensaje de error"));
        $this->_view->setMessage(array("type"=>"info","message"=>"Registro Completado"));
        $this->_view->setMessage(array("type"=>"success","message"=>"Registro Completado"));
        $this->_view->setMessage(array("type"=>"warning","message"=>"Registro Completado"));

//        $this->_registro->registrarUsuario(
//                $this->getSql('nombre'), $this->getAlphaNum('apellido'), $this->getAlphaNum('email'), $this->getSql('pass'), $this->getPostParam('email')
//        );
//
//        if (!$this->_registro->verificarUsuario($this->getAlphaNum('usuario'))) {
//            $this->_view->_error = "Error al registrar el usuario";
//            exit;
//        }
        var_dump($data);
        $this->_view->renderizar('registro', 'usuario');
    }

    public function test($param1, $param2) {
        echo "lalalla";
        var_dump($param1);
        var_dump($param2);
        die;
        $this->_view->renderizar('registro', 'usuario');
    }

}

?>