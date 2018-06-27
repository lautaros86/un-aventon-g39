<?php

class preguntaController extends Controller {
    private $_pregunta;
    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'preguntaModel.php';
        $this->_pregunta = new preguntaModel();
    }

    public function index() {
        
    }
    public function preguntar() {
        Session::set('autenticado', true);
        if (!Session::get('autenticado')) {
            
            $this->redireccionar();
        }
        //cuerpo de preguntar
    }
    public function darDeBajaPregunta($idPregunta) {
        $idPregunta;

    }

    public function responder() {
      
    }

    private function validarDatos() {
     
    }
}
