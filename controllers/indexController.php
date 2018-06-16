<?php

class indexController extends Controller
{
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        
//        require_once ROOT . 'controllers' . DS . 'notificacionController.php';
//        $notificaciones = new notificacionController();
//        $notificaciones->crearNotificacion("el usuario ".Session::get("id_usuario")." cancelo el viaje al que te habias postulado.", array(10, 11, 12, 13, 14));
        $this->_view->renderizar('index', 'index');
    }
}
?>
