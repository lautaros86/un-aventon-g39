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
        Session::set("form", $form);
        $errors=$this->validarAltaVehiculo();

        if (!$errors) {
            try {

                require_once ROOT . 'models' . DS . 'vehiculoModel.php';
                $vehiculoModel = new vehiculoModel();
                $vehiculoModel->insertarVehiculo($form, Session::get("usuario")["id"]);
          
                Session::setMessage("Vehiculo Registrado", SessionMessageType::Success);
                $this->redireccionar("perfil");
            } catch (PDOException $e) {
                Session::setMessage("Error al registrar el vehiculo", SessionMessageType::Error);
            }
        } else {
            $form['marca'] = $this->getAlphaNum('marca');
            $form['modelo'] = $this->getAlphaNum('modelo');
            $form['patente'] = $this->getPostParam('patente');
            $form['asientos'] = $this->getPostParam('asientos');
            $form['baul'] = $this->getAlphaNum('baul');
            Session::set("form", $form);
            Session::set("autenticado", true);
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("registro");
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
          Session::setFormErrors("asientos", "La cantidad de asientos es obligatorio.");
            $errors = true;
        }
        
        return $errors;
    }

}

?>