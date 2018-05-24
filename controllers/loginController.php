<?php

class loginController extends Controller {

    private $_login;

    public function __construct() {
        parent::__construct();
        $this->_login = $this->loadModel('login');
    }

    public function index()
    {
        if (Session::get('autenticado')) 
        {
            $this->redireccionar();
        }
        $this->_view->titulo = 'Iniciar Sesion';
            
            if(!$this->getPostParam('email')){
                $this->_view->_error = 'Debe introducir su mail de usuario';
                $this->_view->renderizar('index','login');
                exit;
            }
            
            if(!$this->getPostParam('pass')){
                $this->_view->_error = 'Debe introducir su password';
                $this->_view->renderizar('index', 'login');
                exit;
            }

            $row = $this->_login->getUsuario(
                    $this->getPostParam('email'),
                    $this->getPostParam('pass')
                    );
            
            if(!$row){
                Session::setMessage('Usuario y/o password incorrectos', SessionMessageType::Error);
                $this->_view->renderizar('index', 'login');
                exit;
            }
            
            if ($row['estado'] == 2 ) {
                //cambiar
                Session::setMessage('Este usuario no esta habilitado', SessionMessageType::Error);
                //$this->_view->_error = 'Este usuario no esta habilitado';
                $this->_view->renderizar('index', 'login');
                exit;
            }

            Session::set('autenticado', true);
            Session::set('level', $row['role']);
            Session::set('usuario', $row['usuario']);
            Session::set('id_usuario', $row['id']);
            Session::set('tiempo', time());
            $this->redireccionar();
        
        $this->_view->renderizar('index','login');
        
    }

    public function cerrar() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        Session::destroy();
        $this->redireccionar();
    }

}

?>