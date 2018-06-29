<?php

class paginaestaticaController extends Controller
{
    public function __construct() {
        parent::__construct();
    }
    public function index() {
        
    }
    public function preguntasFrecuentes()
    {
        $this->_view->renderizar('preguntas', 'index');
    }
}
?>