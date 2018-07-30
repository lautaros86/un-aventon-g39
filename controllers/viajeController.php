<?php

class viajeController extends Controller {

    private $_registro;
    private $_usuario;
    private $_viaje;
    private $_vehiculo;
    private $_factura;
    private $_preguntas;
    private $_QA;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'preguntaModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        require_once ROOT . 'models' . DS . 'viajeModel.php';
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        require_once ROOT . 'models' . DS . 'facturaModel.php';
        require_once ROOT . 'controllers' . DS . 'preguntaController.php';
        $this->_usuario = new usuarioModel();
        $this->_registro = new vehiculoModel();
        $this->_viaje = new viajeModel();
        $this->_vehiculo = new vehiculoModel();
        $this->_factura = new facturaModel();
        $this->_preguntas = new preguntaModel();
        $this->_QA = new preguntaController();
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
        $QA = $this->_QA->verPreguntasYRespuestas($viaje["id"]);
        $params["QA"] = $QA;
        $params["pasajeros"] = $this->_viaje->getPasajeros($viaje["id"]);
        $params["puedePublicarPostular"] = $this->_usuario->calcularPuedePublicarPostular(Session::get("id_usuario"));
        $params["viaje"] = $viaje;
        $params["vehiculo"] = $this->_vehiculo->getVehiculosById($viaje["id_vehiculo"]);
        $params["chofer"] = $this->_usuario->getUsuario($viaje["id_chofer"]);
        $params["chofer"]["cantViajesChofer"] = $this->_viaje->getCantViajesChofer($params["viaje"]["id_chofer"]);
        $params["chofer"]["cantViajesPasajero"] = $this->_viaje->getCantViajesPasajero($params["viaje"]["id_chofer"]);
        $params["usuario"] = Session::get("usuario");
        $params["postulaciones"] = $this->_viaje->getPostulacionesViaje($params["viaje"]["id"]);
        $params["postulacionesAceptadas"] = sizeof($postulacionesAceptadas);
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
        $fechas = $this->getPostParam('fechas');
        $form['fecha'] = $fechas[1][0];
        ;
        $form['hora'] = $fechas[1][1];
        $form['monto'] = $this->getPostParam('monto');
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

            $viajesSuperpuestos = array();
            foreach ($fechas as $key => $value) {
                $date = str_replace('/', '-', $value[0]);
                $datetime = date("Y-m-d", strtotime($date)) . ' ' . $value[1];
                $tmpArray = $this->_viaje->getViajesSupuerpuestos($datetime, $form['duracion']);
                $viajesSuperpuestos = array_merge($viajesSuperpuestos, $tmpArray);
            }
            if (sizeof($viajesSuperpuestos) > 0) {
                Session::setMessage("La fecha y hora se superponen con el viaje nº alguno .", SessionMessageType::Error);
                $errors = true;
            }

            $autoSuperpuestos = array();
            foreach ($fechas as $key => $value) {
                $date = str_replace('/', '-', $value[0]);
                $datetime = date("Y-m-d", strtotime($date)) . ' ' . $value[1];
                $tmpArray = $this->_viaje->getAutosSupuerpuestos($datetime, $form['duracion'], $form['idVehiculo']);
                $autoSuperpuestos = array_merge($autoSuperpuestos, $tmpArray);
            }
            if (sizeof($autoSuperpuestos) > 0) {
                Session::setMessage("El vehiculo elegido esta asosciado a otro viaje en la fecha y hora elegidos.", SessionMessageType::Error);
                $errors = true;
            }

            $postulacionesSuperpuestas = array();
            foreach ($fechas as $key => $value) {
                $date = str_replace('/', '-', $value[0]);
                $datetime = date("Y-m-d", strtotime($date)) . ' ' . $value[1];
                $tmpArray = $this->_viaje->getPostulacionesSupuerpuestos($datetime, $form['duracion'], Session::get("usuario")["id"]);
                $postulacionesSuperpuestas = array_merge($postulacionesSuperpuestas, $tmpArray);
            }
            if (sizeof($postulacionesSuperpuestas) > 0) {
                Session::setMessage("Ud tiene una postulacion aceptada en un viaje que se superpone con la fecha y horas ingresadas.", SessionMessageType::Error);
                $errors = true;
            }

            if (!$errors) {
                try {
                    $this->_viaje->beginTransaction();
                    $idviaje = $this->_viaje->insertarViaje($form, Session::get("usuario")["id"]);
                    foreach ($fechas as $key => $value) {
                        $this->_viaje->setFechas($idviaje, $value[0], $value[1]);
                    }
                    $montoTotal = ($form['monto'] * $form['asientos'] * sizeof($fechas)) * 0.05;
                    $descripcion = "Derecho a publicación del aventon nº " . $idViajeInsertado . " con origen: " . $form['origen'] . " y destino: " . $form['destino'] . ". Con cantidad de asientos: " . $form["asientos"] . " y costo por cada uno de: $" . $form['monto'] . ". Y " . sizeof($fechas) . " repeticiones.";
                    $this->_factura->crearFactura(Session::get("id_usuario"), $idviaje, number_format((float) $montoTotal, 2, '.', ''), $descripcion, 1, 1);
                    Session::setMessage("Pool publicado", SessionMessageType::Success);
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
                $factura = $this->_factura->getFacturasViaje($idViaje);
                if ($factura["id_estado"] != 3) {
                    $this->_factura->activarFacturaDeViaje($idViaje);
                }
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
        //$comentario = $this->getPostParam('comentario');
        $viaje = $this->_viaje->getViaje($idViaje);
        $ultimaFecha = end($viaje["fechas"]);
        $fechaLlegadaEstimada = new DateTime($ultimaFecha["fecha"] . " " . $ultimaFecha["hora"]);
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
        $postulacionesSuperpuestas = array();
        foreach ($viaje["fechas"] as $key => $value) {
            $date = str_replace('/', '-', $value["fecha"]);
            $datetime = date("Y-m-d", strtotime($date)) . ' ' . $value["hora"];
            $tmpArray = $this->_viaje->getPostulacionesSupuerpuestos($datetime, $viaje['duracion'], Session::get("usuario")["id"]);
            $postulacionesSuperpuestas = array_merge($postulacionesSuperpuestas, $tmpArray);
        }
        if (sizeof($postulacionesSuperpuestas) > 0) {
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
                $lleno = $this->_viaje->getPostulacionesAceptadasCant($viaje["id"]) >= $viaje["asientos"];
                if ($lleno) {
                    $this->_viaje->llenarViaje($viaje["id"]);
                    Session::setMessage("El viaje esta lleno.", SessionMessageType::Success);
                }
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
                if ($postulacion["id_estado"] == 2) {
                    $this->_usuario->calificacionAutomatica(Session::get("id_usuario"), -1);
                    $this->_usuario->actualizarReputacion(Session::get("id_usuario"), -1);
                }
                $this->_viaje->rechazarPostulacion($idPostu);
                $lleno = $this->_viaje->getPostulacionesAceptadasCant($viaje["id"]) >= $viaje["asientos"];
                if (!$lleno) {
                    $this->_viaje->desllenarViaje($viaje["id"]);
                }
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
        $date = $this->getPostParam('fecha');
        if ($date != "") {
            $date = str_replace('/', '-', $date);
            $param['fecha'] = date('Y-m-d', strtotime($date));
        }
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
