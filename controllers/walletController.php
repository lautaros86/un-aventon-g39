<?php

class walletController extends Controller {

    private $_wallet;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'walletModel.php';
        $this->_wallet = new walletModel();
    }

    public function index() {
        
    }

    public function cobrar($monto) {
        $saldo = $this->_wallet->getSaldo(Session::get("id_usuario"));
        if ($monto > $saldo) {
            Session::setMessage("Ud no cuenta con el saldo ingresado.", SessionMessageType::Error);
        }else{
            $this->_wallet->extraer(Session::get("id_usuario"), $monto);
            Session::setMessage("Se tranfirieron $" . $monto . " a su cuenta bancaria.", SessionMessageType::Success);
        }
        $this->redireccionar("perfil#wallet");
    }

}

?>