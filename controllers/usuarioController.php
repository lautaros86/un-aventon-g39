<?php

class usuarioController extends Controller {

    private $_registro;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        $this->_registro = new registroModel();
        $this->_usuario = new usuarioModel();
//      $this->_registro = $this->loadModel('registro');
    }

    public function index() {
        
    }

    public function eliminarCuenta() {
        if (Session::get('autenticado')) {
            $this->redireccionar();
        }
        $id = 7;
        try {
            $this->_usuario->eliminarUsuario($id);
            Session::setMessage("La cuenta se elimino exitosamente.", SessionMessageType::Success);
            $this->redireccionar();
        } catch (PDOException $e) {
            Session::setMessage("La cuenta no pudo eliminarse, por favor comuniquese con un administrador.", SessionMessageType::Error);
        } catch (ErrorException $e) {
            Session::setMessage("Error.", SessionMessageType::Error);
        }
    }

    public function registro() {
        if (Session::get('autenticado')) {
            $this->redireccionar();
        }
        $form = Session::get("form");
        Session::destroy("form");
        $this->_view->renderizar('registro', 'usuario', array("form" => $form));
    }

    public function editarPerfil() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $form = Session::get("form");
        Session::destroy("form");
        $this->_view->renderizar('editar', 'usuario', array("form" => $form));
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        $errors = $this->validarRegistro();
        $form = array();
        $form['nombre'] = $this->getAlphaNum('nombre');
        $form['apellido'] = $this->getAlphaNum('apellido');
        $form['fecha_nac'] = $this->getPostParam('fecha_nac');
        $form['email'] = $this->getPostParam('email');
        $form['pass'] = $this->getAlphaNum('pass');
        Session::set("form", $form);
        if (!$errors) {
            try {
                $this->_registro->registrarUsuario(
                        $this->getAlphaNum('nombre'), $this->getAlphaNum('apellido'), $this->getPostParam('email'), $this->getPostParam('fecha_nac'), $this->getPostParam('pass'), $this->getPostParam('email')
                );
                Session::setMessage("Registro Completado", SessionMessageType::Success);
                $this->redireccionar();
            } catch (PDOException $e) {
                Session::setMessage("Error al registrar el usuario", SessionMessageType::Error);
                if (ENV_DEV) {
                    Session::setMessage("Error de desarrollo: " . $e->getMessage(), SessionMessageType::Error);
                }
                $this->redireccionar("registro");
            }
        } else {
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        $this->_view->renderizar('registro', 'usuario', array("form" => $form));
    }

    public function validarRegistro() {
        $errors = false;
        $formErrors = array();
        if ($this->getAlphaNum('nombre') == "") {
            Session::setFormErrors("nombre", "El nombre es obligatorio.");
            $errors = true;
        }

        if ($this->getAlphaNum('apellido') == "") {
            Session::setFormErrors("apellido", "El apellido es obligatorio.");
            $errors = true;
        }

        // TODO: Validar fecha.
        if ($this->getPostParam('fecha_nac') == "") {
            Session::setFormErrors("fecha_nac", "La fecha de nacimiento es obligatoria");
            $errors = true;
        }

        $fecha_nac = DateTime::createFromFormat('d/m/Y', $this->getPostParam('fecha_nac'));
        $fechaLimite = new DateTime('-18 years');
        if (!($fecha_nac < $fechaLimite)) {
            Session::setFormErrors("fecha_nac", "Debe ser mayor de edad para registrarse");
            $errors = true;
        }

        // Valido que sea un email con formato correcto.
        if (!$this->validarEmail($this->getPostParam('email'))) {
            Session::setFormErrors("email", "La direccion de email es inválida");
            $errors = true;
        }

        if ($this->getPostParam('email') == "") {
            Session::setFormErrors("email", "La direccion de email es obligatoria");
            $errors = true;
        }

        // Verifico que el email no exista en el sistema.
        if ($this->_registro->verificarEmail($this->getPostParam('email'))) {
            Session::setFormErrors("email", "La direccion de email ya existe");
            $errors = true;
        }

        if ($this->getPostParam('pass') == "") {
            Session::setFormErrors("pass", "Contraseña incorrecta");
            $errors = true;
        }

        if ($this->getPostParam('repass') == "") {
            Session::setFormErrors("repass", "La contraseña de confirmacion incorrectaa");
            $errors = true;
        }

        if ($this->getPostParam('terminos') != true) {
            Session::setFormErrors("terminos", "Debe aceptar los terminos y condiciones del sitio.");
            $errors = true;
        }

        if ($this->getPostParam('pass') != $this->getPostParam('repass')) {
            Session::setFormErrors("pass", "Los passwords no coinciden");
            Session::setFormErrors("repass", "Los passwords no coinciden");
            $errors = true;
        }

        return $errors;
    }

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("editar");
        }
        $errors = $this->validarRegistro();
        $form = array();
        $form['nombre'] = $this->getAlphaNum('nombre');
        $form['apellido'] = $this->getAlphaNum('apellido');
        $form['fecha_nac'] = $this->getPostParam('fecha_nac');
        //esto no va el mail no se envia
        //$form['email'] = $this->getPostParam('email');
        $form['pass'] = $this->getAlphaNum('pass');
        Session::set("form", $form);
        if (!$errors) {
            try {
//cambiar esta parte para que llame editarUsuario de usuarioModel
//                $this->_registro->registrarUsuario(
//                        $this->getAlphaNum('nombre'), $this->getAlphaNum('apellido'), $this->getPostParam('email'), $this->getPostParam('fecha_nac'), $this->getPostParam('pass'), $this->getPostParam('email')
//                );
                Session::setMessage("Tus datos fueron editados correctamente :)", SessionMessageType::Success);
                $this->redireccionar();
            } catch (PDOException $e) {
                Session::setMessage("Error al editar tus datos :(", SessionMessageType::Error);
            }
        } else {
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("editar");
        }
        $this->_view->renderizar('editar', 'usuario' /* , array("form" => $form) */);
    }

    public function verUsuario() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $vehiculoModel = new vehiculoModel();
        $usuario = Session::get("usuario");
        $vehiculos = $vehiculoModel->getVehiculosByUserId($usuario['id']);
        $this->_view->renderizar('verUsuario', 'usuario', array('usuario' => $usuario, "vehiculos" => $vehiculos));
    }

}

?>