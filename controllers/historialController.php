<?php

class historialController extends Controller {

    private $_registro;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        $this->_registro = new registroModel();
        $this->_usuario = new usuarioModel();
        
    }
    public function index() {
        $this->_view->renderizar('timeline', 'usuario/tabs');
    }

    
}

?>
