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
        if (true){ //si encuentro viajes de un usuario muestro el time line, sino muestro un cartel insitando a que busque un viaje
            $this->_view->renderizar('timeline', 'usuario/tabs');                        
        }
        

        
    }

    
}

?>
