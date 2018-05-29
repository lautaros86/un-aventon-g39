<?php

class vehiculoController extends Controller {

    private $_registro;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $this->_usuario = new usuarioModel();
        $this->_registro = new vehiculoModel();
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
        $this->_view->renderizar('alta', 'vehiculo', array("form" => $form));
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }

        $form = array();
        $form['marca'] = $this->getAlphaNum('marca');
        $form['modelo'] = $this->getAlphaNum('modelo');
        $form['patente'] = $this->getAlphaNum('patente');
        $form['asientos'] = $this->getAlphaNum('asientos');
        $form['baul'] = $this->getAlphaNum('baul');
        $form["baul"] = $form["baul"] == "on" ? 1 : 0;
        Session::set("form", $form);
        $errors = $this->validarAltaVehiculo();

        if (!$errors) {
            try {


                require_once ROOT . 'models' . DS . 'vehiculoModel.php';
                $vehiculoModel = new vehiculoModel();
                $vehiculoModel->insertarVehiculo($form, Session::get("usuario")["id"]);

                Session::setMessage("Vehiculo Registrado", SessionMessageType::Success);
                  Session::destroy("form");
                $this->redireccionar("perfil");
            } catch (PDOException $e) {
                var_dump($e->getMessage()); die;
                Session::setMessage("Error al registrar el vehiculo", SessionMessageType::Error);
                $this->redireccionar("vehiculo/alta");
            }
        } else {
            $form['marca'] = $this->getAlphaNum('marca');
            $form['modelo'] = $this->getAlphaNum('modelo');
            $form['patente'] = $this->getPostParam('patente');
            $form['asientos'] = $this->getPostParam('asientos');
            $form['baul'] = $this->getAlphaNum('baul');
            $form["baul"] = $form["baul"] == "on" ? 1 : 0;
            Session::set("form", $form);
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);

            $this->redireccionar("vehiculo/alta");
        }
        $this->_view->renderizar('alta', 'vehiculo', array("form" => $form));
        
        
    }

    public function lista() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $this->_view->renderizar('lista', 'vehiculo');
    }

    public function validarAltaVehiculo() {
        $errors = false;
        if ($this->getAlphaNum('marca') == "") {
            Session::setFormErrors("marca", "La marca es obligatoria.");
            $errors = true;
        }

        if ($this->getAlphaNum('modelo') == "") {
            Session::setFormErrors("modelo", "El modelo es obligatorio.");
            $errors = true;
        }

        // TODO: Validar fecha.
        if ($this->getPostParam('patente') == "") {
            Session::setFormErrors("patente", "La patente es obligatoria.");
            $errors = true;
        }

        if ($this->getPostParam('asientos') == "") {
            Session::setFormErrors("asientos", "La cantidad de asientos es obligatorio, se debe cargar un dato numerico");
            $errors = true;
        } else {
            if (!is_int($this->verifInt('asientos'))) {
                Session::setFormErrors("asientos", "se debe cargar un dato numerico.");
                $errors = true;
            }
        }

        return $errors;
    }

}

?>