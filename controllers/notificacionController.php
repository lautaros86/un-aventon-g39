<?php

class notificacionController extends Controller {

    private $_notificacionModel;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'notificacionModel.php';
        $this->_notificacionModel = new notificacionModel();
    }

    public function index() {
        
    }

    public function getNotificaciones() {
        $notificaciones = $this->_notificacionModel->getNotificaciones(Session::get("id_usuario"));
        echo json_encode($notificaciones);
    }

    public function limpiarNotificaciones() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        $idsNotificaciones = $this->getPostParam("data");
        $response = array();
        try {

            $response["status"] = $this->_notificacionModel->limpiarNotificaciones(Session::get("id_usuario"), $idsNotificaciones);
        } catch (PDOException $e) {
            $response["status"] = false;
            $response["mensaje"] = $e->getMessage();
        }
        echo json_encode($response);
    }

    public function crearNotificacion($mensaje, $destinatarios, $color = "aqua") {
        try {
            //begin transaction
            $this->_notificacionModel->beginTransaction();
            $this->_notificacionModel->crearNotificacion($mensaje, $destinatarios, $color);
            //commit
            $this->_notificacionModel->commit();
        } catch (PDOException $e) {
            //rollback transaction
            $this->_notificacionModel->rollBack();
        }
    }
}

?>