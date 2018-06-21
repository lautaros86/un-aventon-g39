<?php

class usuarioController extends Controller {

    private $_registro;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        require_once ROOT . 'models' . DS . 'viajeModel.php';
        $this->_registro = new registroModel();
        $this->_usuario = new usuarioModel();
        $this->_viajes = new viajeModel();
    }

    public function index() {
        
    }

    public function eliminarCuenta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $id = Session::get('usuario')["id"];
        $pass = $this->getPostParam("data");
        $requestHasPass = Hash::getHash('sha256', $pass, HASH_KEY);
        $userPass = Session::get("usuario")["password"];
        if ($requestHasPass == $userPass) {
            try {
                $this->_usuario->eliminarUsuario($id);
                Session::setMessage("La cuenta se elimino exitosamente.", SessionMessageType::Success);
                Session::destroy();
                echo json_encode(array("ok" => true, "titulo" => "Cuenta eliminada", "mensaje" => "Su cuenta se elimino con exito. Gracias por haber sido parte del sistema."));
            } catch (PDOException $e) {
                Session::setMessage("La cuenta no pudo eliminarse, por favor comuniquese con un administrador.", SessionMessageType::Error);
            } catch (ErrorException $e) {
                Session::setMessage("Error.", SessionMessageType::Error);
            }
        } else {
            echo json_encode(array("ok" => false, "titulo" => "Oh! Parace que hubo un problema", "mensaje" => "La contraseña no es correcta."));
        }
    }

    public function registro() {
        if (Session::get('autenticado')) {
            $this->redireccionar();
        }
        $form = Session::get("form");
        Session::destroy("form");
        $this->_view->renderizar('registro', 'usuario', array("form" => $form));
    }

    public function editarPerfil() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $usuario = Session::get("usuario");
        $form = Session::get("form");
        Session::destroy("form");
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $usuario['fecha_nac'])) {
            $usuario['fecha_nac'] = date('d/m/Y', strtotime($usuario['fecha_nac']));
        }

        $this->_view->renderizar('editar', 'usuario', array("form" => $usuario));
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        $errors = $this->validarRegistro();
        $form = array();
        $form['nombre'] = $this->getAlphaNum('nombre');
        $form['apellido'] = $this->getPostParam('apellido');
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $usuario['fecha_nac'])) {
            $usuario['fecha_nac'] = date('d/m/Y', strtotime($usuario['fecha_nac']));
        }
        $form['fecha_nac'] = $this->getPostParam('fecha_nac');
        $form['email'] = $this->getPostParam('email');
        $form['pass'] = $this->getAlphaNum('pass');
        Session::set("form", $form);
        if (!$errors) {
            try {
                $this->_registro->registrarUsuario(
                        $this->getPostParam('nombre'), $this->getPostParam('apellido'), $this->getPostParam('email'), $this->getPostParam('fecha_nac'), $this->getPostParam('pass'), $this->getPostParam('email')
                );
                Session::setMessage("Registro Completado", SessionMessageType::Success);
                $this->redireccionar();
            } catch (PDOException $e) {
                Session::setMessage("Error al registrar el usuario", SessionMessageType::Error);
                if (ENV_DEV) {
                    Session::setMessage("Error de desarrollo: " . $e->getMessage(), SessionMessageType::Error);
                }
                $this->redireccionar("registro");
            }
        } else {
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("registro");
        }
        $this->_view->renderizar('registro', 'usuario', array("form" => $form));
    }

    public function validarRegistro() {
        $errors = false;
        $formErrors = array();
        if ($this->getAlphaNum('nombre') == "") {
            Session::setFormErrors("nombre", "El nombre es obligatorio.");
            $errors = true;
        }

        if ($this->getAlphaNum('apellido') == "") {
            Session::setFormErrors("apellido", "El apellido es obligatorio.");
            $errors = true;
        }

        // TODO: Validar fecha.
        if ($this->getPostParam('fecha_nac') == "") {
            Session::setFormErrors("fecha_nac", "La fecha de nacimiento es obligatoria");
            $errors = true;
        }

        $fecha_nac = DateTime::createFromFormat('d/m/Y', $this->getPostParam('fecha_nac'));
        $fechaLimite = new DateTime('-18 years');
        if (!($fecha_nac < $fechaLimite)) {
            Session::setFormErrors("fecha_nac", "Debe ser mayor de edad para registrarse");
            $errors = true;
        }

        // Valido que sea un email con formato correcto.
        if (!$this->validarEmail($this->getPostParam('email'))) {
            Session::setFormErrors("email", "La direccion de email es inválida");
            $errors = true;
        }

        if ($this->getPostParam('email') == "") {
            Session::setFormErrors("email", "La direccion de email es obligatoria");
            $errors = true;
        }

        // Verifico que el email no exista en el sistema.
        if ($this->_registro->verificarEmail($this->getPostParam('email'))) {
            Session::setFormErrors("email", "La direccion de email ya existe");
            $errors = true;
        }

        if ($this->getPostParam('pass') == "") {
            Session::setFormErrors("pass", "Contraseña incorrecta");
            $errors = true;
        }

        if ($this->getPostParam('repass') == "") {
            Session::setFormErrors("repass", "La contraseña de confirmacion incorrectaa");
            $errors = true;
        }

        if ($this->getPostParam('terminos') != true) {
            Session::setFormErrors("terminos", "Debe aceptar los terminos y condiciones del sitio.");
            $errors = true;
        }

        if ($this->getPostParam('pass') != $this->getPostParam('repass')) {
            Session::setFormErrors("pass", "Los passwords no coinciden");
            Session::setFormErrors("repass", "Los passwords no coinciden");
            $errors = true;
        }

        return $errors;
    }

    public function validarDatosDeUsuario() {
        $errors = false;
        $formErrors = array();
        if ($this->getAlphaNum('nombre') == "") {
            Session::setFormErrors("nombre", "El nombre es obligatorio.");
            $errors = true;
        }

        if ($this->getAlphaNum('apellido') == "") {
            Session::setFormErrors("apellido", "El apellido es obligatorio.");
            $errors = true;
        }

        // TODO: Validar fecha.
        if ($this->getPostParam('fecha_nac') == "") {
            Session::setFormErrors("fecha_nac", "La fecha de nacimiento es obligatoria");
            $errors = true;
        }

        $fecha_nac = DateTime::createFromFormat('d/m/Y', $this->getPostParam('fecha_nac'));
        $fechaLimite = new DateTime('-18 years');
        if (!($fecha_nac < $fechaLimite)) {
            Session::setFormErrors("fecha_nac", "Debe ser mayor de edad para registrarse");
            $errors = true;
        }

        return $errors;
    }

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("editar");
        }
        $usuario = Session::get('usuario');
        $errors = $this->validarDatosDeUsuario();
        $form = array();
        $date = $this->getPostParam('fecha_nac');
        $date = str_replace('/', '-', $date);
        $form['fecha_nac'] = date('Y-m-d', strtotime($date));
        $form['nombre'] = $this->getAlphaNum('nombre');
        $form['apellido'] = $this->getPostParam('apellido');


        Session::set("form", $form);
        if (!$errors) {
            try {
//cambiar esta parte para que llame editarUsuario de usuarioModel
                $date = $this->getPostParam('fecha_nac');
                $date = str_replace('/', '-', $date);
                $form['fecha_nac'] = date('Y-m-d', strtotime($date));
                $params = array("id" => $usuario["id"],
                    "nombre" => $this->getAlphaNum('nombre'),
                    "apellido" => $this->getPostParam('apellido'),
                    "fecha" => $form['fecha_nac']
                );
                if ($_FILES['foto']['size'] > 0) {
                    $dirImages = ROOT . 'img/usuarios/';
                    $imgName = str_replace(' ', '_', basename($_FILES['foto']['name']));
                    $dirFile = $dirImages . $imgName;
                    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
                    $file_name = $_FILES['foto']['name'];
                    $file_size = $_FILES['foto']['size'];
                    $type = explode("/", $_FILES['foto']['type'])[1];
                    if (in_array($type, $allowed_ext)) {
                        if ($file_size <= "2048000") {
                            if (!is_null(Session::get("usuario")["foto"])) {
                                if (file_exists(ROOT . Session::get("usuario")["foto"])) {
                                    unlink(ROOT . Session::get("usuario")["foto"]);
                                }
                            }
                            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dirFile)) {
                                $params["foto"] = '/img/usuarios/' . $imgName;
                            } else {
                                Session::setMessage("Error al guardar la imagen.", SessionMessageType::Error);
                            }
                        } else {
                            Session::setMessage("La imagen es muy grande.", SessionMessageType::Error);
                        }
                    } else {
                        Session::setMessage("El formato de imagen es incorrecto.", SessionMessageType::Error);
                    }
                }
                $this->_usuario->editarUsuario($params);
                Session::setMessage("Tus datos fueron editados correctamente :)", SessionMessageType::Success);
                //esta parte se esta tocando
                $newUsuario = Session::get("usuario");
                $newUsuario['nombre'] = $this->getAlphaNum('nombre');
                $newUsuario['apellido'] = $this->getPostParam('apellido');
                $newUsuario['fecha_nac'] = $this->getPostParam('fecha_nac');
                if (isset($params["foto"])) {
                    $newUsuario['foto'] = $params["foto"];
                }
                Session::set("usuario", $newUsuario);
                $this->redireccionar("perfil");
            } catch (PDOException $e) {
                Session::setMessage("Error al editar tus datos :/", SessionMessageType::Error);
            }
        } else {
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("usuario/editarPerfil");
        }
        $this->_view->renderizar('editar', 'usuario', array("form" => $form));
    }

    public function verUsuario() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        require_once ROOT . 'models' . DS . 'vehiculoModel.php';
        $vehiculoModel = new vehiculoModel();
        $usuario = Session::get("usuario");
        $vehiculos = $vehiculoModel->getVehiculosByUserId($usuario['id']);
        $viajes = $this->_viajes->getViajesAbiertos();
        $this->_view->renderizar('verUsuario', 'usuario', array('usuario' => $usuario, "vehiculos" => $vehiculos , "viajes" => $viajes));
    }
    public function verOtroUsuaurio($id_otroUsuario) {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $otroUsuario = $this->_usuario->getUsuario($id_otroUsuario);
        $otroUsuario['cantViajesChofer'] = $this->_viajes->getCantViajesChofer($id_otroUsuario);
        $otroUsuario['cantViajesPasajero'] = $this->_viajes->getCantViajesPasajero($id_otroUsuario);
        $otroUsuario['cantViajesTotal'] = $otroUsuario['cantViajesPasajero'] + $otroUsuario['cantViajesChofer'];
        
        
        $this->_view->renderizar('perfilAjeno', 'usuario', array('usuario' => $otroUsuario));
    }
    public function postular() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar();
        }
        $usuario = Session::get('usuario');
        $idViaje = $this->getAlphaNum("idViaje");
        $idChofer = $this->getAlphaNum("idChofer");
        try {
            $this->_usuario->beginTransaction();
            $this->_notificacion->crearNotificacionSimple("El usuario " . $usuario["nombre"] . " " . $usuario["apellido"] . " se postulo a tu viaje con nº " . $idViaje, $idChofer);
            $this->_usuario->postular($usuario["id"], $idViaje);
            $this->_usuario->commit();
            Session::setMessage("Ud. se postulo exitosamente.", SessionMessageType::Success);
            echo json_encode(array("ok" => true));
        } catch (PDOException $e) {
            $this->_usuario->rollback();
            echo json_encode(array("ok" => false, "mensaje" => $e->getMessage()));
        }
    }

    public function cancelarPostulacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar();
        }
        $usuario = Session::get('usuario');
        $idViaje = $this->getAlphaNum("idViaje");
        $idChofer = $this->getAlphaNum("idChofer");
        try {
            $this->_usuario->beginTransaction();
            $this->_notificacion->crearNotificacionSimple("El usuario " . $usuario["nombre"] . " " . $usuario["apellido"] . " cancelo su postulacion al viaje nº " . $idViaje, $idChofer);
            $this->_usuario->cancelarPostulacion($usuario["id"], $idViaje);
            $this->_usuario->commit();
            echo json_encode(array("ok" => true));
        } catch (PDOException $e) {
            $this->_usuario->rollback();
            echo json_encode(array("ok" => false, "mensaje" => $e->getMessage()));
        }
    }

    public function editarContrasenia() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setMessage("Intento de acceso incorrecto a la funcion.", SessionMessageType::Error);
            $this->redireccionar("editar");
        }
        $usuario = Session::get('usuario');
        $errors = $this->validarContrasenia($usuario["id"]);
        $form = array();

        $form['contraseniaNueva'] = $this->getPostParam('contraseniaNueva');
        $form['contraseniaVieja'] = $this->getPostParam('contraseniaVieja');
        $form['repeatPassNuevo'] = $this->getPostParam('repeatPassNuevo');
        Session::set("form", $form);
        if (!$errors) {
            try {
                $this->_usuario->editarUsuarioContrasenia($usuario["id"], $form['contraseniaNueva']);
                Session::setMessage("Tu contraseña fue editada correctamente :)", SessionMessageType::Success);
            } catch (PDOException $e) {
                Session::setMessage("Error al editar tus datos :/", SessionMessageType::Error);
            }
        } else {
            Session::setMessage("Por favor corriga los errores del formulario que estan resaltados en rojo", SessionMessageType::Error);
            $this->redireccionar("usuario/editarPerfil");
        }
        $this->_view->renderizar('editar', 'usuario', array("form" => $form));
    }

    public function validarContrasenia($id) {
        $errors = false;
        $formErrors = array();
        $oldPass = Hash::getHash('sha256', $this->getPostParam('contraseniaVieja'), HASH_KEY);
        //$a = $oldPass -> Hash::getHash('sha256', $oldPasss, HASH_KEY);
        try {
            $pass = $this->_usuario->getUsuario($id);
        } catch (PDOException $e) {
            Session::setMessage("Error al editar tus datos :/", SessionMessageType::Error);
        }
        if ($this->getPostParam('contraseniaNueva') == "") {


            Session::setFormErrors("contraseniaNueva", "este campo no puede estar vacío", SessionMessageType::Error);
            $errors = true;
        }
        if ($this->getPostParam('contraseniaVieja') == "") {
            Session::setFormErrors("contraseniaVieja", "este campo no puede estar vacío");
            $errors = true;
        } else {
            if ($pass['password'] != $oldPass) {
                Session::setFormErrors("contraseniaVieja", "Lo lamento está no es tu contraseña actual");
                $errors = true;
            }
        }
        if ($this->getPostParam('repeatPassNuevo') == "") {
            Session::setFormErrors("repeatPassNuevo", "este campo no puede estar vacío");
            $errors = true;
        }
        if ($this->getPostParam('contraseniaNueva') != $this->getPostParam('repeatPassNuevo')) {
            Session::setFormErrors("contraseniaNueva", "Las contraseñas no coinciden");
            Session::setFormErrors("repeatPassNuevo", "Las contraseñas no coinciden");
            $errors = true;
        }

        return $errors;
    }

}

?>