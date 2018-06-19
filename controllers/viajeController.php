<?php

class viajeController extends Controller {

    private $_registro;
    private $_usuario;
    private $_viaje;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        require_once ROOT . 'models' . DS . 'viajeModel.php';
        $this->_usuario = new usuarioModel();
        $this->_registro = new vehiculoModel();
        $this->_viaje = new viajeModel();
    }

    public function index() {
        $this->lista();
    }

    public function alta() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $vehiculoModel = new vehiculoModel();
        $params["vehiculos"] = $vehiculoModel->getVehiculosOfUser(Session::get("id_usuario"));
        if (sizeof($params["vehiculos"]) == 0) {
            Session::setMessage("No tienes vehiculos para publicar un viaje.", SessionMessageType::Error);
            $this->redireccionar('perfil');
        }
        $form = Session::get("form");
        Session::destroy("form");
        $this->_view->renderizar('alta', 'viaje', array("form" => $form, "params" => $params));
    }

    public function detalle($idviaje) {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $viaje = $this->_viaje->getViaje($idviaje);
        if (empty($viaje)) {
            Session::setMessage("El viaje requerido no existe.", SessionMessageType::Error);
            $this->redireccionar("perfil");
        }
        $params["viaje"] = $this->_viaje->getViaje($idviaje);
        $chofer = $this->_usuario->getUsuario($viaje["id_chofer"]);
        $usuario = Session::get("usuario");
        if ($chofer["id"] == $usuario["id"]) {
            $this->detalleChofer($viaje, $chofer, $usuario);
        } else {
            $this->detallePostulante($viaje, $chofer, $usuario);
        }
    }

    private function detallePostulante($viaje, $chofer, $usuario) {
        $params["viaje"] = $viaje;
        $params["chofer"] = $chofer;
        $params["chofer"]["cantViajesChofer"] = $this->_viaje->getCantViajesChofer($params["viaje"]["id_chofer"]);
        $params["chofer"]["cantViajesPasajero"] = $this->_viaje->getCantViajesPasajero($params["viaje"]["id_chofer"]);
        $params["esChofer"] = false;
        $postulaciones = $this->_viaje->getPostulacionesViaje($viaje["id"]);
        foreach ($postulaciones as $postu) {
            if ($postu["id_pasajero"] == $usuario["id"]) {
                $params["postulado"] = true;
            }
        }
        $this->_view->renderizar('detalle', 'viaje', $params);
    }

    private function detalleChofer($viaje, $chofer, $usuario) {
        $params["viaje"] = $viaje;
        $params["chofer"] = $chofer;
        $params["chofer"]["cantViajesChofer"] = $this->_viaje->getCantViajesChofer($params["viaje"]["id_chofer"]);
        $params["chofer"]["cantViajesPasajero"] = $this->_viaje->getCantViajesPasajero($params["viaje"]["id_chofer"]);
        $params["chofer"] = false;
        $params["usuario"] = $usuario;
        $postulaciones = $this->_viaje->getPostulacionesOf($idviaje);
        $params["postulado"] = false;
        foreach ($postulaciones as $postu) {
            if ($postu["id_usuairo"] == $usuario["id"]) {
                $params["postulado"] = true;
            }
        }
        $this->_view->renderizar('detalle', 'viaje', $params);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $form = array();
        $form['monto'] = $this->getPostParam('monto');
        $form['fecha'] = $this->getPostParam('fecha');
        $form['hora'] = $this->getPostParam('hora');
        $form['origen'] = $this->getPostParam('origen');
        $form['destino'] = $this->getPostParam('destino');
        $form['idVehiculo'] = $this->getPostParam('idVehiculo');
        $form["asientos"] = $this->verifInt('asientos');
        Session::set("form", $form);
        $errors = $this->validarAltaViaje($form);
        if (!$errors) {
            try {
                require_once ROOT . 'models' . DS . 'viajeModel.php';
                $viajeModel = new viajeModel();
                $viajeModel->insertarViaje($form, Session::get("usuario")["id"]);
                Session::setMessage("Viaje publicado", SessionMessageType::Success);
                Session::destroy("form");
                $this->redireccionar("perfil");
            } catch (PDOException $e) {
                Session::setMessage("Error al registrar el viaje", SessionMessageType::Error);
                $this->redireccionar("viaje/alta");
            }
        } else {
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("viaje/alta");
        }
        $this->_view->renderizar('alta', 'viaje', array("form" => $form));
    }

    public function validarAltaViaje($form) {
        $errors = false;
        if ($form['monto'] == "") {
            Session::setFormErrors("monto", "El monto es obligatorio.");
            $errors = true;
        } elseif ($form['monto'] <= 0) {
            Session::setFormErrors("monto", "El monto debe ser mayor a 0.");
            $errors = true;
        }
        if ($form['origen'] == "") {
            Session::setFormErrors("origen", "El origen es obligatorio.");
            $errors = true;
        }

        if ($form['destino'] == "") {
            Session::setFormErrors("destino", "El destino es obligatorio.");
            $errors = true;
        }
        // TODO: Validar fecha.
        if ($form['fecha'] == "") {
            Session::setFormErrors("fecha", "La fecha es obligatoria.");
            $errors = true;
        }
        if ($form['hora'] == "") {
            Session::setFormErrors("hora", "La hora es obligatoria.");
            $errors = true;
        }
        if ($form['idVehiculo'] == "") {
            Session::setFormErrors("idVehiculo", "El vehiculo es obligatoria.");
            $errors = true;
        }

        if ($form['asientos'] == "") {
            Session::setFormErrors("asientos", "La cantidad de asientos es obligatorio, se debe cargar un dato numerico");
            $errors = true;
        } else {
            if (!is_int($form['asientos'])) {
                Session::setFormErrors("asientos", "se debe cargar un dato numerico.");
                $errors = true;
            }
        }

        return $errors;
    }

}

?>