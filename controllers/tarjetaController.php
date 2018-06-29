<?php

class tarjetaController extends Controller {

    private $_tarjeta;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'tarjetaModel.php';
        $this->_tarjeta = new tarjetaModel();
        $this->_usuario = new usuarioModel();
    }

    public function index() {
        $this->lista();
    }

    public function lista() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $this->_view->renderizar('lista', 'tarjeta');
    }

    public function alta() {
        Session::set('autenticado', true);
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $form = Session::get("form");
        Session::destroy("form");

        $params = $this->cargardatos();
        $params["form"] = $form;
        $this->_view->renderizar('alta', 'tarjeta', $params);
    }

    /**
     * VALIDA QUE EL NUMERO DE LA TARJETA NO SEA INGRESADO DOS VECES
     * @param type $form
     * @return boolean
     */
    public function validarNumeroRepetido($numeroTarjeta, $idUsuario) {
        $err = $this->_tarjeta->consultarPorRepetido($numeroTarjeta, $idUsuario);
        if ($err['cantidad'] != 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * recibe un parametro y da de baja una tarjeta
     * @param type $id
     */
    public function darDeBajaTarjeta($idTarjeta) {
        $idUsuario = Session::get("id_usuario");
        try {
            $this->_tarjeta->darDeBajaTarjeta($idTarjeta, $idUsuario);
            Session::setMessage("Tarjeta dada de baja", SessionMessageType::Success);
            $this->redireccionar("perfil");
        } catch (Exception $exc) {
            Session::setMessage("No se pudo dar de baja la tarjeta", SessionMessageType::Error);
            $this->redireccionar("perfil");
        }
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("perfil");
        }
        $id = Session::get("usuario")["id"];
        $form = array();
        $form['numero'] = $this->getPostParam('numero');
        //tengo que validar que el numero tenga hasta 16 digitos
        $form['nombre'] = $this->getPostParam('nombre');
        $form['mesVencimiento'] = $this->getPostParam('mesVencimiento');
        $form['anioVencimiento'] = $this->getPostParam('anioVencimiento');
        $form['entidad'] = $this->getPostParam('entidad');
        Session::set("form", $form);
        $errors = $this->validarDatosFormulario();
        if (!$errors) {
            try {

                if (!($this->validarNumeroRepetido($form['numero'], $id))) {
                    Session::setMessage("Esta tarjeta ya se encuentra cargada", SessionMessageType::Error);
                    $this->redireccionar("tarjeta/alta");
                }
                $this->_tarjeta->beginTransaction();
                $this->_tarjeta->insertarTarjeta($form, (int) $id);
                Session::setMessage("Tarjeta registrada correctamente", SessionMessageType::Success);
                Session::destroy("form");
                $this->_tarjeta->commit();
                $this->redireccionar("perfil");
            } catch (PDOException $e) {
                $this->_tarjeta->rollBacks();
                Session::setMessage("Error al registrar la tarjeta", SessionMessageType::Error);
                $this->redireccionar("tarjeta/alta");
            }
        } else {
            $form['numero'] = $this->getPostParam('numero');
            $form['nombre'] = $this->getPostParam('nombre');
            $form['mesVencimiento'] = $this->getPostParam('mesVencimiento');
            $form['anioVencimiento'] = $this->getPostParam('anioVencimiento');

            Session::set("form", $form);
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("tarjeta/alta");
        }
        $params = $this->cargardatos();
        $params["form"] = $form;
        $this->_view->renderizar('alta', 'vehiculo', $params);
    }

    public function validarDatosFormulario() {
        $errors = false;
        $numero = $this->getPostParam('numero');
        $mes = strlen($this->getPostParam('mesVencimiento'));
        $anio = strlen($this->getPostParam('anioVencimiento'));
        $patron_1 = '/^((4[0-9]{12})|(4[0-9]{15})|(5[1-5][0-9]{14})|(3[47][0-9]{13})|(6011[0-9]{12}))$/';
        if ($this->getPostParam('nombre') == "") {
            Session::setFormErrors("nombre", "El nombre es obligatorio.");
            $errors = true;
        }

        if ($this->getPostParam('numero') == "") {
            Session::setFormErrors("numero", "El número es obligatorio.");
            $errors = true;
        } elseif (!(preg_match($patron_1, $numero))) {
            Session::setFormErrors("numero", "El número no pertenece a una entidad bancaria.");
            $errors = true;
        }
        if ($this->getPostParam('mesVencimiento') == "") {
            Session::setFormErrors("mesVencimiento", "El mes de vencimiento es obligatorio.");
            $errors = true;
        }
        if ($mes != 2 || $mes == 1) { //valido la longitud del campo
            Session::setFormErrors("mesVencimiento", "Solo se aceptan dos digitos.");
            $errors = true;
        }
        if ($this->getPostParam('mesVencimiento') > 12) {//valida que el numero del mes no sea mayor de 12
            Session::setFormErrors("mesVencimiento", "Número del mes incorrecto.");
            $errors = true;
        }
        if ($mes == 00) {//valida que no se ponga 00 como mes
            Session::setFormErrors("mesVencimiento", "Número del mes incorrecto.");
            $errors = true;
        }
        if (!(preg_match("/^[0-9]{2}$/", $this->getPostParam('mesVencimiento')))) {
            Session::setFormErrors("mesVencimiento", "Solo se aceptan caracteres numericos.");
            $errors = true;
        }
        if (!(preg_match("/^[0-9]{2}$/", $this->getPostParam('anioVencimiento')))) {
            Session::setFormErrors("anioVencimiento", "Solo se aceptan caracteres numericos.");
            $errors = true;
        }
        if ($this->getPostParam('anioVencimiento') == "") {
            Session::setFormErrors("anioVencimiento", "El mes de vencimiento es obligatorio.");
            $errors = true;
        }
        if ($anio != 2) {
            Session::setFormErrors("anioVencimiento", "solo se aceptan dos digitos.");
            $errors = true;
        }
        return $errors;
    }

}
