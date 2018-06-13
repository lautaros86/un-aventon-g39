<?php

class usuarioController extends Controller {

    private $_registro;
    private $_usuario;

    public function __construct() {
        parent::__construct();
        require_once ROOT . 'models' . DS . 'registroModel.php';
        require_once ROOT . 'models' . DS . 'usuarioModel.php';
        $this->_registro = new registroModel();
        $this->_usuario = new usuarioModel();
//      $this->_registro = $this->loadModel('registro');
    }
    public function index() {
        
    }

    public function eliminarCuenta() {
        if (!Session::get('autenticado')) {
            $this->redireccionar();
        }
        $id = Session::get('usuario')["id"];
        try {
            $this->_usuario->eliminarUsuario($id);
            Session::setMessage("La cuenta se elimino exitosamente.", SessionMessageType::Success);
            Session::destroy();
            $this->redireccionar();
        } catch (PDOException $e) {
            Session::setMessage("La cuenta no pudo eliminarse, por favor comuniquese con un administrador.", SessionMessageType::Error);
        } catch (ErrorException $e) {
            Session::setMessage("Error.", SessionMessageType::Error);
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
        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$usuario['fecha_nac']))
        {
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
        $date = $this->getPostParam('fecha_nac');
        $date = str_replace('/', '-', $date);
        $form['fecha_nac'] = date('Y-m-d', strtotime($date));
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
                    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
                    $file_name = $_FILES['foto']['name'];
                    $file_size = $_FILES['foto']['size'];
                    $file_tmp = $_FILES['foto']['tmp_name'];
                    $type = pathinfo($file_tmp, PATHINFO_EXTENSION);
                    $data = file_get_contents($file_tmp);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $params["foto"] = $base64;
                }
                $this->_usuario->editarUsuario($params);
                Session::setMessage("Tus datos fueron editados correctamente :)", SessionMessageType::Success);
                //esta parte se esta tocando
                $newUsuario = Session::get("usuario");
                $newUsuario['nombre'] = $this->getAlphaNum('nombre');
                $newUsuario['apellido'] = $this->getPostParam('apellido');
                $newUsuario['fecha_nac'] = $this->getPostParam('fecha_nac');
                if (isset($base64)) {
                    $newUsuario['foto'] = $base64;
                }
                Session::destroy("usuario");
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
        $this->_view->renderizar('verUsuario', 'usuario', array('usuario' => $usuario, "vehiculos" => $vehiculos));
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
        }else{
            if ($pass['password'] != $oldPass){
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