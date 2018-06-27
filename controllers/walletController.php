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

}

?>