<?php

class vehiculoController extends Controller {

    private $_registro;
    private $_vehiculo;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $this->_vehiculo = new vehiculoModel();
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

    public function validarPatente($form) {
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $vehiculoModel = new vehiculoModel();
        $err = $vehiculoModel->consultarPatente($form);
        if ($err['cantidad'] > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function eliminarVehiculo($id) {
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $vehiculoModel = new vehiculoModel();
        $vehiculoModel->darDeBaja($id);
        $vehiculos = $vehiculoModel->getVehiculosByUserId(Session::get("id_usuario"));
        if(!(sizeof($vehiculos) > 0)){
            Session::set('chofer', false);
        }
        Session::setMessage("Vehiculo dado de baja", SessionMessageType::Success);
        $this->redireccionar("perfil");
    }
    
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }

        $form = array();
        $form['patente'] = $this->getAlphaNum('patente');
        if ($this->validarPatente($form)) {
            $form['marca'] = $this->getAlphaNum('marca');
            $form['modelo'] = $this->getAlphaNum('modelo');

            $form['asientos'] = $this->getAlphaNum('asientos');
            $form['baul'] = $this->getAlphaNum('baul');
            $form["baul"] = $form["baul"] == "on" ? 1 : 0;
            Session::set("form", $form);
            $errors = $this->validarAltaVehiculo();
        } else
            $errors = 1;
        if ($errors == 1) {
            Session::setFormErrors("patente", "Esa patente Ud. la tiene cargada.");
        }
        if (!$errors) {
            try {
                require_once ROOT . 'models' . DS . 'vehiculoModel.php';
                $vehiculoModel = new vehiculoModel();
                $vehiculoModel->insertarVehiculo($form, Session::get("usuario")["id"]);
                Session::setMessage("Vehiculo Registrado", SessionMessageType::Success);
                Session::destroy("form");
                Session::set('chofer', true);
                $this->redireccionar("perfil");
            } catch (PDOException $e) {
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

    public function getVehiculoJson() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }

        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $idVehiculo = $this->getPostParam("idVehiculo");
        $vehiculo = $this->_vehiculo->getVehiculo($idVehiculo);
        echo json_encode(array("vehiculo" => $vehiculo));
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