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
        $validarDeuda = $this->_factura->getFacturasActivasOf(Session::get("id_usuario"));
        if (sizeof($validarDeuda) > 0) {
            Session::setMessage("Disculpe, pero para publicar un nuevo viaje primero debe abonar las facutas pendientes. Puede verificar cuales son en la seccion de 'Mis facturas'", SessionMessageType::Error);
            $this->redireccionar('perfil');
        }
        $params["vehiculos"] = $this->_vehiculo->getVehiculosActivosByUserId(Session::get("id_usuario"));
        if (sizeof($params["vehiculos"]) == 0) {
            Session::setMessage("No tienes vehiculos para publicar un viaje.", SessionMessageType::Error);
            $this->redireccionar('perfil');
        }
        $form = Session::get("form");
        Session::destroy("form");
        $this->_view->renderizar('alta', 'viaje', array("form" => $form, "params" => $params));
    }

    public function listado() {
        $params = array();
        $params["viajes"] = $this->_viaje->getViajesPublicos();
        $this->_view->renderizar('listado', 'viaje', $params);
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
        $postulacionesAceptadas = $this->_viaje->getPostulacionesAceptadas($viaje["id"]);
        if (in_array($viaje["id_estado"], [2, 5])) {
            $destinatarios = array();
            foreach ($postulacionesAceptadas as $postulante) {
                $destinatarios[] = $postulante["id_pasajero"];
            }
            if ($viaje["id_chofer"] != Session::get("id_usuario") && !in_array(Session::get("id_usuario"), $destinatarios)) {
                Session::setMessage("El viaje requerido solo puede ser visto por el chofer y los pasajeros.", SessionMessageType::Error);
                $this->redireccionar("perfil");
            }
        }
        $params["pasajeros"] = $this->_viaje->getPasajeros($viaje["id"]);
        $params["puedePublicarPostular"] = $this->_usuario->calcularPuedePublicarPostular(Session::get("id_usuario"));
        $params["viaje"] = $viaje;
        $params["vehiculo"] = $this->_vehiculo->getVehiculosById($viaje["id_vehiculo"]);
        $params["chofer"] = $this->_usuario->getUsuario($viaje["id_chofer"]);
        $params["chofer"]["cantViajesChofer"] = $this->_viaje->getCantViajesChofer($params["viaje"]["id_chofer"]);
        $params["chofer"]["cantViajesPasajero"] = $this->_viaje->getCantViajesPasajero($params["viaje"]["id_chofer"]);
        $params["usuario"] = Session::get("usuario");
        $params["postulaciones"] = $this->_viaje->getPostulacionesViaje($params["viaje"]["id"]);
        if ($params["chofer"]["id"] == $params["usuario"]["id"]) {
            $params["esChofer"] = true;
            $this->_view->renderizar('detalle', 'viaje', $params);
        } else {
            $params["esChofer"] = false;
            $this->detallePostulante($params);
        }
    }

    private function detallePostulante($params) {
        $postulaciones = $this->_viaje->getPostulacionesViaje($params["viaje"]["id"]);
        $params["postulado"] = false;
        foreach ($postulaciones as $postu) {
            if ($postu["id_pasajero"] == $params["usuario"]["id"]) {
                $params["postulacion"] = $postu;
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
        $form['duracion'] = $this->getPostParam('duracion');
        $form['origen'] = $this->getPostParam('origen');
        $form['destino'] = $this->getPostParam('destino');
        $form['idVehiculo'] = $this->getPostParam('idVehiculo');
        $form["asientos"] = $this->verifInt('asientos');
        Session::set("form", $form);
        $errors = $this->validarAltaViaje($form);
        $validarDeuda = $this->_factura->getFacturasActivasOf(Session::get("id_usuario"));
        if (sizeof($validarDeuda) > 0) {
            Session::setMessage("Disculpe, pero para publicar un nuevo viaje primero debe abonar las facutas pendientes. Puede verificar cuales son en la seccion de 'Mis facturas'", SessionMessageType::Error);
        }
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
                    $facturaModel->crearFactura(Session::get("id_usuario"), $idViajeInsertado, number_format((float) $montoTotal, 2, '.', ''), $descripcion, 1, 1);
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

    public function finalizarviaje($idViaje) {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $viaje = $this->_viaje->getViaje($idViaje);
        $fechaLlegadaEstimada = new DateTime($viaje["fecha"] . " " . $viaje["hora"]);
        $fechaLlegadaEstimada->add(new DateInterval('PT' . $viaje["duracion"] . 'S')); // adds 674165 secs
        $datenow = new DateTime();
        $tiempoAceptable = $fechaLlegadaEstimada < $datenow;
        if (!$tiempoAceptable) {
            Session::setMessage("No se puede finalizar el viaje hasta que pase un tiempo prudencial (" . $fechaLlegadaEstimada->format('d/m/Y H:i') . ").", SessionMessageType::Error);
            $this->redireccionar("viaje/detalle/" . $viaje["id"]);
        }
        if ($viaje["id_chofer"] == Session::get("usuario")["id"] && $tiempoAceptable) {
            $pasajeros = $this->_viaje->getPasajeros($idViaje);
            try {
                $this->_viaje->beginTransaction();
                $destinatarios = array();
                foreach ($pasajeros as $pasajero) {
                    $destinatarios[] = $pasajeros["id_pasajero"];
                    $this->_viaje->finalizarPostulacion($pasajero["id_postulacion"]);
                    $this->_factura->crearFactura($pasajero["id_pasajero"], $idViaje, $viaje["monto"], "por viajar", 2, 2);
                    $this->_notificacion->crearNotificacionSimple("Tu factura con n°" . $this->_viaje->lastInsertId() . "para el viaje n° " . $viaje["id"] . " esta disponible.", $pasajero["id_pasajero"], "green");
                    $this->_usuario->crearCalificacion($idViaje, $viaje["id_chofer"], $pasajero["id_pasajero"]);
                    $this->_usuario->crearCalificacion($idViaje, $pasajero["id_pasajero"], $viaje["id_chofer"]);
                }
                $this->_notificacion->crearNotificacion("El viaje <a href='/viaje/detalle/" . $viaje["id"] . "'>n°  " . $viaje["id"] . "</a> finalizo.", $destinatarios, "green");
                $this->_notificacion->crearNotificacion("Recuerda calificar al chofer por el viaje n° " . $viaje["id"] . ".", $destinatarios, "green");
                $this->_notificacion->crearNotificacionSimple("El viaje <a href='/viaje/detalle/" . $viaje["id"] . "'>n°  " . $viaje["id"] . "</a> finalizo.", $viaje["id_chofer"], "green");
                $this->_notificacion->crearNotificacionSimple("Recuerda calificar a los pasajeros/as de tu viaje n° " . $viaje["id"] . ".", $viaje["id_chofer"], "green");
                $this->_viaje->finaizarViaje($idViaje);
                Session::setMessage("El viaje se finalizo exitosamente.", SessionMessageType::Success);
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
        if (!in_array($viaje["id_estado"], [1, 4])) {
            $error = true;
            Session::setMessage("Solo puede aceptar postulaciones cuando el viaje esta ABIERTO o LLENO.", SessionMessageType::Error);
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
        if (!in_array($viaje["id_estado"], [1, 4])) {
            Session::setMessage("Solo puede rechazar postulaciones cuando el viaje esta ABIERTO o LLENO.", SessionMessageType::Error);
            $this->redireccionar("/viaje/detalle/" . $postulacion["id_viaje"]);
        }
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
