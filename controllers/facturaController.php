<?php

class facturaController extends Controller {

    private $_factura;
    private $_wallet;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'facturaModel.php';
        $this->_factura = new facturaModel();
        require_once ROOT . 'models' . DS . 'walletModel.php';
        $this->_wallet = new walletModel();
    }

    public function index() {
        
    }

    public function pagarTarjeta($idFactura) {
        $factura = $this->_factura->getFactura($idFactura);
        try {
            $this->_factura->beginTransaction();
            if ($factura["id_tipo"] == 2) {
                $this->_wallet->depositar($factura["id_chofer"], $factura["monto"]);
            }
            $this->_factura->pagarFactura($idFactura, "tarjeta");
            $this->_notificacion->crearNotificacionSimple("Se pago la factura nº " . $idFactura . ".", Session::get("id_usuario"), "green");
            Session::setMessage("La factura se pago correctamente.", SessionMessageType::Success);
            $this->_factura->commit();
        } catch (PDOException $e) {
            $this->_factura->rollback();
            Session::setMessage("Hubo un problema al realizar el pago.", SessionMessageType::Success);
        }
        $this->redireccionar("perfil#facturas");
    }

    public function pagarWallet($idFactura) {
        $saldo = $this->_wallet->getSaldo(Session::get("id_usuario"));
        $factura = $this->_factura->getFactura($idFactura);
        if ($saldo < $factura["monto"]) {
            Session::setMessage("Su saldo es insuficiente para le pago de la factura. Por favor intente con otro medio de pago.", SessionMessageType::Error);
        } else {
            try {
                $this->_factura->beginTransaction();
                if ($factura["id_tipo"] == 2) {
                    $this->_wallet->extraer(Session::get("id_usuario"), $factura["monto"]);
                    $this->_wallet->depositar($factura["id_chofer"], $factura["monto"]);
                }
                $this->_wallet->extraer(Session::get("id_usuario"), $factura["monto"]);
                $this->_factura->pagarFactura($idFactura, "wallet");
                $this->_notificacion->crearNotificacionSimple("Se pago la factura nº " . $idFactura . ".", Session::get("id_usuario"), "green");
                Session::setMessage("La factura se pago correctamente.", SessionMessageType::Success);
                $this->_factura->commit();
            } catch (PDOException $e) {
                $this->_factura->rollback();
                Session::setMessage("Hubo un problema al realizar el pago.", SessionMessageType::Success);
            }
        }
        $this->redireccionar("perfil#facturas");
    }

}

?>