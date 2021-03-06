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
        $params = $this->cargardatos();
        $params["form"] = $form;
        $this->_view->renderizar('alta', 'vehiculo', $params);
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

    public function editar($id) {
        $form = $this->_vehiculo->getVehiculosById($id);
        $params = $this->cargardatos();
        $params["vehiculo"] = $form;
        $this->_view->renderizar('modificar', 'vehiculo', $params);
    }

    public function modificar() {
        $form['idVehiculo'] = $this->getAlphaNum('idVehiculo');
        $form['marca'] = $this->getAlphaNum('marca');
        $form['modelo'] = $this->getAlphaNum('modelo');
        $form['patente'] = $this->getPostParam('patente');
        $form['asientos'] = $this->getPostParam('asientos'); //falta esto
        $form['baul'] = $this->getAlphaNum('baul'); //falta esto
        $form["baul"] = $form["baul"] == "on" ? 1 : 0;
        Session::set("form", $form);
        $errors= $this->validarAltaVehiculo();
        if (!$errors) {
            try {
                $this->_vehiculo->modificar($form);
                Session::setMessage("Vehiculo Modificado", SessionMessageType::Success);
                Session::destroy("form");
                $this->redireccionar("perfil#misVehiculos");
            } catch (PDOException $e) {
                Session::setMessage("Error al registrar el vehiculo", SessionMessageType::Error);
                $this->redireccionar("vehiculo/modificar");
            }
        } else {
            Session::set("form", $form);
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("vehiculo/editar/".$form['idVehiculo']);
        }
        $params = $this->cargardatos();
        $params["form"] = $form;
        $this->_view->renderizar('modificar', 'vehiculo', $params);
    }

    public function eliminarVehiculo($id) {
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $vehiculoModel = new vehiculoModel();
        $viajesActivosAsociados = $this->_vehiculo->getViajesByVehiculoId($id);
        if (sizeof($viajesActivosAsociados) > 0) {
            Session::setMessage("No se puede eliminar un vehiculo asociado a viajes pendientes", SessionMessageType::Error);
            $this->redireccionar("perfil#misVehiculos");
        }
        $vehiculoModel->darDeBaja($id);
        $vehiculos = $vehiculoModel->getVehiculosActivosByUserId(Session::get("id_usuario"));
        if (!(sizeof($vehiculos) > 0)) {
            Session::set('chofer', false);
        }
        Session::setMessage("Vehiculo dado de baja", SessionMessageType::Success);
        $this->redireccionar("perfil#misVehiculos");
    }

    public function restoreVehiculo($id) {
        $this->_vehiculo->restoreVehiculo($id);
        $vehiculos = $this->_vehiculo->getVehiculosActivosByUserId(Session::get("id_usuario"));
        if (!(sizeof($vehiculos) > 0)) {
            Session::set('chofer', false);
        }
        Session::setMessage("Vehiculo dado de baja", SessionMessageType::Success);
        $this->redireccionar("perfil#misVehiculos");
    }

    public function ajaxVerificarPatente($patente) {
        $vehiculo = $this->_vehiculo->getByPatente($patente);
        $response = array();
        $response["ok"] = false;
        if ($vehiculo) {
            // solo nueva vinculacion.
            $duenios = $this->_vehiculo->getDueniosDe($vehiculo["id"]);
            $idDuenios = array();
            foreach ($duenios as $duenio) {
                $idDuenios[] = $duenio["id_usuario"];
            }
            if (in_array(Session::get("id_usuario"), $idDuenios)) {
                $response["mensaje"] = "<p>Esa patente ya esta asociada a su cuenta.</p>";
                $response["ok"] = true;
            } else {
                $response["ok"] = true;
                $response["mensaje"] = "<p>El sistema detecto que la patente que esta ingresando ya esta cargada por otro/s usuarios.</p>
                    <p>Si desea asociar este vehiculo a su cuenta, puede enviar el formulario con la patente y este se vinculara con ud.</p>
                    <p>Caso contrario recomendamos cambie la patente.</p>   ";
            }
        }
        echo json_encode($response);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar();
        }
        $form = array();
        $form['patente'] = $this->getAlphaNum('patente');
        $form['marca'] = $this->getAlphaNum('marca');
        $form['modelo'] = $this->getAlphaNum('modelo');
        $form['asientos'] = $this->getAlphaNum('asientos');
        $form["baul"] = $this->getAlphaNum('baul') == "on" ? 1 : 0;
        Session::set("form", $form);
        $vehiculo = $this->_vehiculo->getByPatente($form['patente']);
        if ($vehiculo) {
            // solo nueva vinculacion.
            $duenios = $this->_vehiculo->getDueniosDe($vehiculo["id"]);
            $idDuenios = array();
            foreach ($duenios as $duenio) {
                $idDuenios[] = $duenio["id_usuario"];
            }
            if (in_array(Session::get("id_usuario"), $idDuenios)) {
                Session::setFormErrors("patente", "Esa patente Ud. la tiene cargada.");
            } else {
                try {
                    $this->_vehiculo->beginTransaction();
                    $this->_notificacion->crearNotificacion("El usuario " . Session::get("usuario")["nombre"] . " " . Session::get("usuario")["apellido"] . " agrego el vehiculo con patente " . $form['patente'], $idDuenios);
                    $this->_vehiculo->agragerDuenioPara(Session::get("id_usuario"), $vehiculo["id"]);
                    Session::setMessage("Vehiculo Registrado", SessionMessageType::Success);
                    $this->_vehiculo->commit();
                } catch (PDOException $e) {
                    echo $e->getMessage();

                    $this->_vehiculo->rollback();
                    Session::setMessage("Error al registrar el vehiculo", SessionMessageType::Error);
                    $this->redireccionar("vehiculo/alta");
                }
            }
        } else {
            $errors = $this->validarAltaVehiculo();
            if (!$errors) {
                try {
                    $this->_vehiculo->beginTransaction();
                    $this->_vehiculo->insertarVehiculo($form, Session::get("usuario")["id"]);
                    Session::setMessage("Vehiculo Registrado", SessionMessageType::Success);
                    Session::destroy("form");
                    Session::set('chofer', true);
                    $this->_vehiculo->commit();
                    $this->redireccionar("perfil#misVehiculos");
                } catch (PDOException $e) {
                    $this->_vehiculo->rollBack();
                    Session::setMessage("Error al registrar el vehiculo", SessionMessageType::Error);
                    $this->redireccionar("vehiculo/alta");
                }
            } else {
                Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
                $this->redireccionar("vehiculo/alta");
            }
        }
        $params = $this->cargardatos();
        $params["form"] = $form;
        $this->_view->renderizar('alta', 'vehiculo', $params);
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
        $vehiculo = $this->_vehiculo->getVehiculosById($idVehiculo);
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
        if (!is_int($this->verifInt('modelo'))) {
            Session::setFormErrors("modelo", "El modelo es un año.");
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