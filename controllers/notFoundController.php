<?php

class notFoundController extends Controller
{
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $this->_view->titulo = '404 - Not Found';
        $this->_view->renderizar('index', "404");
    }
}
?>
