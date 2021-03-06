<?php

class Router {

    private $_controlador;
    private $_metodo;
    private $_argumentos;
    private $_routes;
    private static $instancia;

    private function __construct() {
        $this->processURI();
    }

    public static function getInstance() {
        if (!self::$instancia instanceof self) {
            self::$instancia = new self;
        }
        return self::$instancia;
    }

    private function processURI() {
        $this->_routes = json_decode(ROUTES, true);

        $uri = isset($_GET['url']) ? $_GET['url'] : '/';

        // Evaluar si es util o no.
        //        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        //            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        //        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
        //            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        //        }
        //        $parts = preg_split('#\?#i', $uri, 2);
        //
        //        $uri = $parts[0];
        //        if (isset($parts[1])) {
        //            $_SERVER['QUERY_STRING'] = $parts[1];
        //            parse_str($_SERVER['QUERY_STRING'], $_GET);
        //        } else {
        //            $_SERVER['QUERY_STRING'] = '';
        //            $_GET = array();
        //        }
        //-------
        if ($uri == '')
            $uri = '/';
        $uri = parse_url($uri, PHP_URL_PATH);

        $bad = array('$', '(', ')', '%28', '%29');
        $good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');
        $this->URI = array();
        foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $uri)) as $val) {
            $val = str_replace($bad, $good, $val);
            if ($val != '')
                $this->URI[] = $val;
        }

        $this->parse_routes();

        $this->_controlador = strtolower(array_shift($this->URI));

        $this->_metodo = strtolower(array_shift($this->URI));

        $this->_argumentos = $this->URI;
    }

    private function parse_routes() {
        // Turn the segment array into a URI string
        $uri = strtolower(implode('/', $this->URI));

        // Is there a literal match?  If so we're done
        if (isset($this->_routes[$uri])) {
            $this->URI = array();
            foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->_routes[$uri])) as $val) {
                if ($val != '')
                    $this->URI[] = $val;
            }
            return true;
        }

        // Loop through the route array looking for wild-cards
        foreach ($this->_routes as $key => $val) {
            // Convert wild-cards to RegEx
            $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

            // Does the RegEx match?
            if (preg_match('#^' . $key . '$#', $uri)) {
                // Do we have a back-reference?
                // echo strpos($key, '(');
                if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE) {
                    $val = preg_replace('#^' . $key . '$#', $val, $uri);
                }

                $this->URI = array();
                foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $val)) as $v) {
                    if ($v != '')
                        $this->URI[] = $v;
                }
                return true;
            }
        }
    }

    public function getControlador() {
        if ($this->_controlador != null) {
            return $this->_controlador;
        } else {
            return DEFAULT_CONTROLLER;
        }
    }

    public function getMetodo() {
        if ($this->_metodo != null) {
            return $this->_metodo;
        } else {
            return DEFAULT_METHOD;
        }
    }

    public function getArgs() {
        return $this->_argumentos;
    }

}
?>

