<?php

class historialController extends Controller {

    private $_viajes;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'historialModel.php';
        $this->_viajes = new historialModel();
    }

    public function index() {
        $id = Session::get('usuario')["id"];
        $viajes = $this->_viajes->getViajesRealizados($id);
        if (!empty($viajes)) {
            $this->_view->renderizar('timeline', 'usuario/tabs', array("viajes" => $viajes));
        } else {
            $this->_view->renderizar('noTimeline', 'usuario/tabs');
        }
    }
    
    public function postulaciones() {
        $id = Session::get('usuario')["id"];
        $postulaciones = $this->_viajes->getPostulaciones($id);
        if (!empty($postulaciones)) {
            $this->_view->renderizar('postulados', 'usuario/tabs', array("postulaciones" => $postulaciones));
        }  
    }

}