<?php

class usuarioController extends Controller {

    private $_registro;
    private $_generarsesion;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'generarSesionModel.php';
        $this->_registro = new registroModel();
        $this->_generarsesion = new generarSesionModel();
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

    public function iniciarsesion()
    {
        # code...
        //primer parametro es el archivo segundo es la carpeta
    
        $this->_view->renderizar('iniciarsesion', 'usuario');
    }

    
    public function obtenersesion (){
        $errors = false;
        $data = $_POST;

        if ($this->getAlphaNum('nombreDeUsuario') == "") {
            $this->_view->setMessage(array("type" => "danger", "message" => "Debe ingresar un nombre de usuario."));
            $errors = true;
        }
        if ($this->getPostParam('pass') == "") {
            $this->_view->setMessage(array("type" => "danger", "message" => "Ingrese una contraseña incorrecta"));
            $errors = true;
        }   
        if (!$errors) {
            try {
                $this->_generarsesion->obtenerUsuario(
                        $this->getAlphaNum('nombreDeUsuario'), $this->getPostParam('pass') );
              
            } catch (PDOException $e) {
                $this->_view->setMessage(array("type" => "danger", "message" => "Error al comprobar los datos, intente nuevamente"));
            }
        }         

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