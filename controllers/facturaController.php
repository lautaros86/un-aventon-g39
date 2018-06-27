<?php

class facturaController extends Controller {

    private $_factura;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'facturaModel.php';
        $this->_factura = new facturaModel();
    }

    public function index() {
        
    }

    public function pagar($idFactura) {
        try {
            $this->_factura->beginTransaction();
            $this->_factura->pagarFactura($idFactura);
            $this->_notificacion->crearNotificacionSimple("Se pago la factura nº " . $idFactura . ".", Session::get("id_usuario"), "green");
            Session::setMessage("La factura se pago correctamente.", SessionMessageType::Success);
            $this->_factura->commit();
        } catch (PDOException $e) {
            $this->_factura->rollback();
            Session::setMessage("Hubo un problema al realizar el pago.", SessionMessageType::Success);
        }
        $this->redireccionar("perfil#facturas");
    }

}

?>