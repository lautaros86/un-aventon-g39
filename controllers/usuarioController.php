<?php

class usuarioController extends Controller {

    private $_registro;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        $this->_registro = new registroModel();
//        $this->_registro = $this->loadModel('registro');
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
        $errors = false;
        $data = $_POST;
        if ($this->getAlphaNum('nombre') == "") {
            $this->_view->setMessage(array("type" => "danger", "message" => "El nombre es obligatorio."));
            $errors = true;
        }

        if ($this->getAlphaNum('apellido') == "") {
            $this->_view->setMessage(array("type" => "danger", "message" => "El apellido es obligatorio."));
            $errors = true;
        }

        // TODO: Validar fecha.
        // Valido que sea un email con formato correcto.
        if (!$this->validarEmail($this->getPostParam('email'))) {
            $this->_view->setMessage(array("type" => "danger", "message" => "La direccion de email es inválida"));
            $errors = true;
        }

        if ($this->getPostParam('email') == "") {
            $this->_view->setMessage(array("type" => "danger", "message" => "La direccion de email es obligatoria"));
            $errors = true;
        }

        // Verifico que el email no exista en el sistema.
        if ($this->_registro->verificarEmail($this->getPostParam('email'))) {
            $this->_view->setMessage(array("type" => "danger", "message" => "La direccion de email ya existe"));
            $errors = true;
        }

        if ($this->getPostParam('pass') == "") {
            $this->_view->setMessage(array("type" => "danger", "message" => "Contraseña incorrecta"));
            $errors = true;
        }

        if ($this->getPostParam('repass') == "") {
            $this->_view->setMessage(array("type" => "danger", "message" => "La contraseña de confirmacion incorrectaa"));
            $errors = true;
        }

        if ($this->getPostParam('pass') != $this->getPostParam('repass')) {
            $this->_view->setMessage(array("type" => "danger", "message" => "Los passwords no coinciden"));
            $errors = true;
        }

        if (!$errors) {
            try {
                $this->_registro->registrarUsuario(
                        $this->getAlphaNum('nombre'), $this->getAlphaNum('apellido'), $this->getPostParam('email'), $this->getPostParam('fecha_nac'), $this->getPostParam('pass'), $this->getPostParam('email')
                );
                $this->_view->setMessage(array("type" => "success", "message" => "Registro Completado"));
            } catch (PDOException $e) {
                $this->_view->setMessage(array("type" => "danger", "message" => "Error al registrar el usuario"));
            }
        }
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