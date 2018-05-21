<?php

class vehiculoController extends Controller {

    private $_registro;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        $this->_usuario = new usuarioModel();
    }

    public function index() {
        $this->lista();
    }

    public function alta() {
        Session::set('autenticado', true);
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $form = Session::get("form");
        Session::destroy("form");
        $this->_view->renderizar('registro', 'usuario', array("form" => $form));
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }

        $errors = $this->validarRegistro();
        $form = array();
        if (!$errors) {
            try {
                $this->_registro->registrarUsuario(
                        $this->getAlphaNum('nombre'), $this->getAlphaNum('apellido'), $this->getPostParam('email'), $this->getPostParam('fecha_nac'), $this->getPostParam('pass'), $this->getPostParam('email')
                );
                Session::setMessage("Registro Completado", SessionMessageType::Success);
                $this->redireccionar("/");
            } catch (PDOException $e) {
                Session::setMessage("Error al registrar el usuario", SessionMessageType::Error);
            }
        } else {
            $form['nombre'] = $this->getAlphaNum('nombre');
            $form['apellido'] = $this->getAlphaNum('apellido');
            $form['fecha_nac'] = $this->getPostParam('fecha_nac');
            $form['email'] = $this->getPostParam('email');
            Session::set("form", $form);
            Session::set("autenticado", true);
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        $this->_view->renderizar('registro', 'usuario', array("form" => $form));
    }

    public function lista(){
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $this->_view->renderizar('lista', 'vehiculo');
    }
    public function validarAltaVehiculo() {
        $errors = false;
        if ($this->getAlphaNum('nombre') == "") {
            $this->_view->setFormError("nombre", "El nombre es obligatorio.");
            $errors = true;
        }

        if ($this->getAlphaNum('apellido') == "") {
            $this->_view->setFormError("apellido", "El apellido es obligatorio.");
            $errors = true;
        }

        // TODO: Validar fecha.
        if ($this->getPostParam('fecha_nac') == "") {
            $this->_view->setFormError("fecha_nac", "La fecha de nacimiento es obligatoria");
            $errors = true;
        }

        $fecha_nac = DateTime::createFromFormat('d/m/Y', $this->getPostParam('fecha_nac'));
        $fechaLimite = new DateTime('-18 years');
        if (!($fecha_nac < $fechaLimite)) {
            $this->_view->setFormError("fecha_nac", "Debe ser mayor de edad para registrarse");
        }

        // Valido que sea un email con formato correcto.
        if (!$this->validarEmail($this->getPostParam('email'))) {
            $this->_view->setFormError("email", "La direccion de email es inválida");
            $errors = true;
        }

        if ($this->getPostParam('email') == "") {
            $this->_view->setFormError("email", "La direccion de email es obligatoria");
            $errors = true;
        }

        // Verifico que el email no exista en el sistema.
        if ($this->_registro->verificarEmail($this->getPostParam('email'))) {
            $this->_view->setFormError("email", "La direccion de email ya existe");
            $errors = true;
        }

        if ($this->getPostParam('pass') == "") {
            $this->_view->setFormError("pass", "Contraseña incorrecta");
            $errors = true;
        }

        if ($this->getPostParam('repass') == "") {
            $this->_view->setFormError("repass", "La contraseña de confirmacion incorrectaa");
            $errors = true;
        }

        if ($this->getPostParam('terminos') != true) {
            $this->_view->setFormError("terminos", "Debe aceptar los terminos y condiciones del sitio.");
            $errors = true;
        }

        if ($this->getPostParam('pass') != $this->getPostParam('repass')) {
            $this->_view->setFormError("pass", "Los passwords no coinciden");
            $errors = true;
        }

        return $errors;
    }

}

?>