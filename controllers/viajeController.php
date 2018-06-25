<?php

class viajeController extends Controller {

    private $_registro;
    private $_usuario;
    private $_viaje;
    private $_vehiculo;
    private $_factura;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        require_once ROOT . 'models' . DS . 'viajeModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        require_once ROOT . 'models' . DS . 'facturaModel.php';
        $this->_usuario = new usuarioModel();
        $this->_registro = new vehiculoModel();
        $this->_viaje = new viajeModel();
        $this->_vehiculo = new vehiculoModel();
        $this->_factura = new facturaModel();
    }

    public function index() {
        $this->lista();
    }

    public function alta() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $vehiculoModel = new vehiculoModel();
        $params["vehiculos"] = $vehiculoModel->getVehiculosActivosByUserId(Session::get("id_usuario"));
        if (sizeof($params["vehiculos"]) == 0) {
            Session::setMessage("No tienes vehiculos para publicar un viaje.", SessionMessageType::Error);
            $this->redireccionar('perfil');
        }
        $form = Session::get("form");
        Session::destroy("form");
        $this->_view->renderizar('alta', 'viaje', array("form" => $form, "params" => $params));
    }

    public function detalle($idviaje) {
        // $this->blockMinutesRound(gmdate("H:i:s", 758946), 1);
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $viaje = $this->_viaje->getViaje($idviaje);
        if (empty($viaje)) {
            Session::setMessage("El viaje requerido no existe.", SessionMessageType::Error);
            $this->redireccionar("perfil");
        }
        if ($viaje["id_estado"] == 3) {
            Session::setMessage("El viaje requerido fue eliminado.", SessionMessageType::Error);
            $this->redireccionar("perfil");
        }
        if (in_array($viaje["id_estado"], [2, 5])) {
            $postulacionesAceptadas = $this->_viaje->getPostulacionesAceptadas($viaje["id"]);
            $destinatarios = array();
            foreach ($postulantes as $postulante) {
                $destinatarios[] = $postulante["id"];
            }
            if (!in_array(Session::get("id_usuario"), $destinatarios)) {
                Session::setMessage("El viaje requerido solo puede ser visto por el chofer y los pasajeros.", SessionMessageType::Error);
                $this->redireccionar("perfil");
            }
        }
        $params["viaje"] = $viaje;
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
        $params["postulado"] = false;
        foreach ($postulaciones as $postu) {
            if ($postu["id_pasajero"] == $usuario["id"]) {
                $params["postulacion"] = $postu;
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
        $params["esChofer"] = true;
        $params["usuario"] = $usuario;
        $params["postulaciones"] = $this->_viaje->getPostulacionesViaje($params["viaje"]["id"]);
        $params["postulacionesAceptadas"] = $this->_viaje->getPostulacionesAceptadasCant($params["viaje"]["id"]);
        $params["vehiculo"] = $this->_vehiculo->getVehiculosActivosByUserId($viaje["id_vehiculo"]);
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
        $form['duracion'] = $this->getPostParam('duracion');
        $form['origen'] = $this->getPostParam('origen');
        $form['destino'] = $this->getPostParam('destino');
        $form['idVehiculo'] = $this->getPostParam('idVehiculo');
        $form["asientos"] = $this->verifInt('asientos');
        Session::set("form", $form);
        $errors = $this->validarAltaViaje($form);
        if (!$errors) {

            $date = str_replace('/', '-', $form['fecha']);
            $datetime = date("Y-m-d", strtotime($date)) . ' ' . $form['hora'];
            $viajesSuperpuestos = $this->_viaje->getViajesSupuerpuestos($datetime, $form['duracion']);
            if (sizeof($viajesSuperpuestos) > 0) {
                Session::setMessage("La fecha y hora se superponen con el viaje nº alguno .", SessionMessageType::Error);
                $errors = true;
            }
            $autoSuperpuestos = $this->_viaje->getAutosSupuerpuestos($datetime, $form['duracion'], $form['idVehiculo']);
            if (sizeof($autoSuperpuestos) > 0) {
                Session::setMessage("El vehiculo elegido esta asosciado a otro viaje en la fecha y hora elegidos.", SessionMessageType::Error);
                $errors = true;
            }
            $postulacionesSuperpuestas = $this->_viaje->getPostulacionesSupuerpuestos($datetime, $form['duracion'], Session::get("usuario")["id"]);
            if (sizeof($postulacionesSuperpuestas) > 0) {
                Session::setMessage("Ud tiene una postulacion aceptada en un viaje que se superpone con la fecha y horas ingresadas.", SessionMessageType::Error);
                $errors = true;
            }
            if (!$errors) {
                try {
                    $this->_viaje->beginTransaction();
                    $this->_viaje->insertarViaje($form, Session::get("usuario")["id"]);
                    $idViajeInsertado = $this->_viaje->lastInsertId();
                    require_once ROOT . 'models' . DS . 'facturaModel.php';
                    $facturaModel = new facturaModel();
                    $montoTotal = ($form['monto'] * $form['asientos']) * 0.05;
                    $descripcion = "Derecho a publicación del viaje nº " . $idViajeInsertado . " con origen: " . $form['origen'] . " y destino: " . $form['destino'] . ". Con cantidad de asientos: " . $form["asientos"] . " y costo por cada uno de: $" . $form['monto'];
                    $facturaModel->crearFactura(Session::get("id_usuario"), $idViajeInsertado, number_format((float) $montoTotal, 2, '.', ''), $descripcion, 1);
                    Session::setMessage("Viaje publicado", SessionMessageType::Success);
                    Session::destroy("form");
                    $this->_viaje->commit();
                    $this->redireccionar("perfil");
                } catch (PDOException $e) {
                    $this->_viaje->rollback();
                    Session::setMessage("Error al registrar el viaje", SessionMessageType::Error);
                    $this->redireccionar("viaje/alta");
                }
            } else {
                $this->redireccionar("viaje/alta");
            }
        } else {
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("viaje/alta");
        }
        $this->_view->renderizar('alta', 'viaje', array("form" => $form));
    }

    public function cancelarViaje($idViaje) {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $viaje = $this->_viaje->getViaje($idViaje);
        if ($viaje["id_chofer"] == Session::get("usuario")["id"]) {
            $postulantes = $this->_viaje->getPostulacionesViaje($idViaje);
            $postulantesAceptados = $this->_viaje->getPostulacionesAceptadas($idViaje);
            try {
                $this->_viaje->beginTransaction();
                $destinatarios = array();
                foreach ($postulantes as $postulante) {
                    $destinatarios[] = $postulante["id"];
                    $this->_viaje->rechazarPostulacion($postulante["id_postulacion"]);
                }
                $this->_notificacion->crearNotificacion("El usuario nombre apelldo cancelo el viaje al que te habias postulado", $destinatarios, "red");
                if (sizeof($postulantesAceptados) > 0) {
                    $calificacion = (sizeof($postulantesAceptados) + 1) * -1;
                    $this->_notificacion->crearNotificacionSimple("Haz sido penalizado con " . $calificacion . " punto de reputacion por cancelar una postulacion aceptada al viaje nº " . $viaje["id"], $viaje["id_chofer"]);
                    $this->_usuario->calificacionAutomatica($viaje["id_chofer"], $calificacion);
                    $this->_usuario->actualizarReputacion($viaje["id_chofer"], $calificacion);
                }
                $this->_factura->activarFacturaDeViaje($idViaje);
                $this->_viaje->cancelarViaje($idViaje);
                $this->_notificacion->crearNotificacionSimple("La factura del viaje nº " . $viaje["id"] . " esta lista para ser pagada.", $viaje["id_chofer"]);
                Session::setMessage("El viaje se cancelo exitosamente.", SessionMessageType::Success);
                $this->_viaje->commit();
                $this->redireccionar("perfil");
            } catch (PDOException $e) {
                $this->_viaje->rollback();
                Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            }
        } else {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
        }
    }

    public function aceptarPostulacion($idPostu) {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $error = false;
        $postulacion = $this->_viaje->getPostulacion($idPostu);
        $pasajero = $this->_usuario->getUsuario($postulacion["id_pasajero"]);
        $viaje = $this->_viaje->getViaje($postulacion["id_viaje"]);
        $chofer = $this->_usuario->getUsuario($viaje["id_chofer"]);
        $lleno = $this->_viaje->getPostulacionesAceptadasCant($viaje["id"]) >= $viaje["asientos"];
        if ($lleno) {
            $error = true;
            Session::setMessage("No puede aceptarse esta postulacion, el viaje esta lleno.", SessionMessageType::Error);
        }
        // TODO: Validar que el pasajero de la postulacion no tenga otra postulacion aceptada para un viaje en el mismo dia y rango de 2 horas del que se lo esta aceptando.
        $timestamp = strtotime($viaje["hora"]) + 120 * 60;
        $horaFin = date('H:i' . ':00', $timestamp);
        $viajesSuperpuestos = $this->_viaje->validarSuperposicionDeViajesConPostulaciones($pasajero["id"], $viaje["fecha"], $viaje["hora"], $horaFin, $idPostu, $viaje["id"]);
        if ($viajesSuperpuestos > 0) {
            $error = true;
            Session::setMessage("No puede aceptarse esta postulacion porque al usuario se le acepto otra postulacion en un viaje que se superpone con este, si desea puede rechazarlo.", SessionMessageType::Error);
        }
        if ($viaje["id_chofer"] != Session::get("id_usuario")) {
            $error = true;
            Session::setMessage("No puede aceptarse esta postulacion, solo el dueño puede aceptar publicaciones.", SessionMessageType::Error);
        }
        if ($postulacion["id_estado"] == 3) {
            $error = true;
            Session::setMessage("No puede aceptarse esta postulacion porque ya fue rechazada.", SessionMessageType::Error);
        }
        if ($postulacion["id_estado"] == 4) {
            $error = true;
            Session::setMessage("No puede aceptarse esta postulacion porque fue cancelada por el usuario.", SessionMessageType::Error);
        }
        if (!$error) {
            try {
                $this->_viaje->beginTransaction();
                $this->_notificacion->crearNotificacionSimple("El usuario " . $chofer["nombre"] . " " . $chofer["apellido"] . " acepto tu postulacion al viaje nº " . $postulacion["id_viaje"], $pasajero["id"]);
                $this->_viaje->aceptarPostulacion($idPostu);
                $this->_viaje->commit();
                Session::setMessage("La postulacion se acepto correctamente.", SessionMessageType::Success);
            } catch (PDOException $e) {
                $this->_viaje->rollback();
                Session::setMessage("Error al intentar guardar la postulacion.", SessionMessageType::Error);
            }
        }
        $this->redireccionar("/viaje/detalle/" . $postulacion["id_viaje"]);
    }

    public function rechazarPostulacion($idPostu) {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $postulacion = $this->_viaje->getPostulacion($idPostu);
        $pasajero = $this->_usuario->getUsuario($postulacion["id_pasajero"]);
        $viaje = $this->_viaje->getViaje($postulacion["id_viaje"]);
        $chofer = $this->_usuario->getUsuario($viaje["id_chofer"]);
        if ($viaje["id_chofer"] == Session::get("id_usuario") && $postulacion["id_estado"] != 3) {
            try {
                $this->_viaje->beginTransaction();
                $this->_notificacion->crearNotificacionSimple("El usuario " . $chofer["nombre"] . " " . $chofer["apellido"] . " rechazo tu postulacion al viaje nº " . $postulacion["id_viaje"], $pasajero["id"]);
                $this->_viaje->rechazarPostulacion($idPostu);
                $this->_viaje->commit();
                Session::setMessage("La postulacion se rechazo correctamente.", SessionMessageType::Success);
            } catch (PDOException $e) {
                $this->_viaje->rollback();
                Session::setMessage("Error al intentar rechazar la postulacion.", SessionMessageType::Error);
            }
        } else {
            Session::setMessage("No puede rechazarse esta postulacion.", SessionMessageType::Error);
        }
        $this->redireccionar("/viaje/detalle/" . $postulacion["id_viaje"]);
    }

    public function validarAltaViaje($form) {
        $errors = false;
        if ($form['monto'] == "") {
            Session::setFormErrors("monto", "El monto es obligatorio.");
            $errors = true;
        } elseif ($form['monto'] <= 0) {
            Session::setFormErrors("monto", "El monto debe ser mayor a 0.");
            $errors = true;
        } elseif (!preg_match('/^[0-9]*$/', $form['monto'])) {
            Session::setFormErrors("monto", "El monto debe ser un número entero.");
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

    /**
     * esta funcion recibe un arreglo con, fecha, origen y destino de un viaje
     * en base a esos datos consulta en la base de datos por aquellos viajes
     * que esten abiertos, que cumplan con los requisitos y renderiza una vista
     * con los resultados.
     * @param array $param
     */
    public function buscarViaje() {
        $param = array();
        $param['origen'] = $this->getPostParam('origen');
        $param['destino'] = $this->getPostParam('destino');
        $param['fecha'] = date('Y-m-d', strtotime($this->getPostParam('fecha')));
        $viajes = $this->_viaje->buscarViaje($param);
        //pregunto si el array no esta vacio 
        if (!empty($viajes)) {
            //y el usuario esta autenticado
            if (Session::get('autenticado')) {
                //renderizo los resultados para personas legueadas
                $this->_view->renderizar('resultadoBusqueda', 'viaje', array("viajes" => $viajes));
            } else {
                //sino muestro los resultados para personas no logueadas
                $this->_view->renderizar('resultado', 'viaje', array("viajes" => $viajes));
            }
        } else {
            $this->_view->renderizar('noResultadoBusqueda', 'viaje');
        }
    }

}
