<?php

class View {

    private $controlador;
    private $twig;
    private $_js;

    public function __construct(Request $peticion) {
        $this->_controlador = $peticion->getControlador();
        $this->controlador = strtolower($peticion->getControlador());
        $loader = new Twig_Loader_Filesystem(VIE_PATH);
        $this->twig = new Twig_Environment($loader, array('debug' => true));
        $this->twig->addExtension(new Twig_Extension_Debug());
        // $this->session = new Session();
        $this->_js = array();
    }

    public function renderizar($vista, $item = false, $args = array()) {
        $menu = array(
            array(
                'id' => 'inicio',
                'titulo' => 'inicio',
                'enlace' => BASE_URL
            ),
            array(
                'id' => 'post',
                'titulo' => 'Post',
                'enlace' => BASE_URL . 'post'
            )
        );

        if (Session::get('autenticado')) {
            $menu[] = array(
                'id' => 'login',
                'titulo' => 'Cerrar Sesion',
                'enlace' => BASE_URL . 'login/cerrar'
            );
        } else {
            $menu[] = array(
                'id' => 'login',
                'titulo' => 'Iniciar Sesion',
                'enlace' => BASE_URL . 'login'
            );
            $menu[] = array(
                'id' => 'registro',
                'titulo' => 'Registrar Usuario',
                'enlace' => BASE_URL . 'registro'
            );
        }

        $js = array();

        if (count($this->_js)) {
            $js = $this->_js;
        }

        $_layoutParams = array(
            'ruta_css' => BASE_URL . 'views/layout/' . DEFAULT_LAYOUT . '/css/',
            'ruta_img' => BASE_URL . 'views/layout/' . DEFAULT_LAYOUT . '/img/',
            'ruta_js' => BASE_URL . 'views/layout/' . DEFAULT_LAYOUT . '/js/',
            'menu' => $menu,
            'js' => $js
        );

        //para luego probar
        //$template = file_get_contents($rutaView);
        //var_dump($template);
        //print $template;


        $rutaView = $this->controlador . DS . $vista . '.html.twig';
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

}

?>