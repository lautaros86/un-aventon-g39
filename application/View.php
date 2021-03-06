<?php

class View {

    private $controlador;
    private $twig;
    private $_message;
    private $_errors;
    private $_notificaciones;

    public function __construct(Router $router) {
        $this->_controlador = $router->getControlador();
        $this->controlador = strtolower($router->getControlador());
        $loader = new Twig_Loader_Filesystem(VIE_PATH);
        $this->twig = new Twig_Environment($loader, array('debug' => true));
        $this->twig->addExtension(new Twig_Extension_Debug());
        $this->_js = array();
        $this->_notificaciones = array();
    }

    public function renderizar($vista, $dir = "", $args = array()) {
        if ($dir == "") {
            $rutaView = $this->controlador . DS . $vista . '.html.twig';
        } else {
            $rutaView = $dir . DS . $vista . '.html.twig';
        }
        $args['errors'] = $this->_errors;
        $args['errors'] = Session::getFormErrors();
        $args['notificaciones'] = $this->_notificaciones;

        $args['messages'] = $this->_message;
        $args['messages'] = Session::getMessages();
        $args['session'] = new Session();

        if (sizeof($args) > 0) {
            echo $this->twig->render($rutaView, $args);
        } else {
            echo $this->twig->render($rutaView);
        }
    }

    public function setJs(array $js) {
        if (is_array($js) && count($js)) {
            for ($i = 0; $i < count($js); $i++) {
                $this->_js[] = BASE_URL . 'views/' . $this->_controlador . '/js/' . $js[$i] . '.js';
            }
        } else {
            throw new Exception('Error de js');
        }
    }

    public function setFormError($elem, $msg) {
        $this->_errors[$elem][] = $msg;
    }

    public function getFormError() {
        return $this->_errors;
    }

    public function setNotificacion($notificaciones) {
        $this->_notificaciones = $notificaciones;
    }

    public function getNotificacion() {
        return $this->_notificaciones;
    }

}

?>