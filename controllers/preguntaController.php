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
    public function preguntar($idRequester, $idViaje) {
        if (!Session::get('autenticado')) {            
            $this->redireccionar();
        }
        $pregunta = $this->getPostParam('pregunta');
        $error = $this->validarDatos($pregunta); 
        if (!$error){
            try {
                $param['pregunta'] = $pregunta;
                $param['idRequester']=$idRequester;
                $param['idViaje']=$idViaje;
                $this->_pregunta->preguntar($param);
                Session::setMessage("Tu pregunta fue publicada.", SessionMessageType::Success);                
                $this->redireccionar("/viaje/detalle/".$idViaje);
            } catch (Exception $e) {
                Session::setMessage("Lo sentimos ocurrio un error vuelva intentarlo", SessionMessageType::Error);
                $this->redireccionar("perfil");
            }
        }else {
            Session::setMessage("Por favor ingrese un pregunta", SessionMessageType::Error);
            $this->redireccionar("/viaje/detalle/".$idViaje);
        }        
    }
    public function responder($idPregunta,$idViaje) {
        $respuesta = $this->getPostParam('respuesta');
        $error = $this->validarDatos($respuesta); 
        if (!$error){
            try {
                $param['respuesta'] = $respuesta;
                $param['id_pregunta']=$idPregunta;
                $this->_pregunta->responder($param);
                Session::setMessage("Tu respuesta fue publicada.", SessionMessageType::Success);                
                $this->redireccionar("/viaje/detalle/".$idViaje);
            } catch (Exception $e) {
                Session::setMessage("Lo sentimos ocurrio un error vuelva intentarlo", SessionMessageType::Error);
                $this->redireccionar("/viaje/detalle/".$idViaje);
            }
            Session::setMessage("Por favor ingrese un mensaje de respuesta", SessionMessageType::Error);
            $this->redireccionar("/viaje/detalle/".$idViaje);
        }else {
            Session::setMessage("Por favor ingrese un mensaje de respuesta", SessionMessageType::Error);
            $this->redireccionar("/viaje/detalle/".$idViaje);
        }
    }
    public function removeQuestion($idPregunta, $idViaje) {
        if (!Session::get('autenticado')) {            
            $this->redireccionar();
        }
        try{
            $this->_pregunta->eliminarPregunta($idPregunta);
            Session::setMessage("La pregunta fue eliminada.", SessionMessageType::Success);                
            $this->redireccionar("/viaje/detalle/".$idViaje);
        } catch (Exception $ex) {
            Session::setMessage("Ocurrio un error, vuelva a intentarlo.", SessionMessageType::Error);                
            $this->redireccionar("/viaje/detalle/".$idViaje);
        }
        

    }

    public function verPreguntasYRespuestas($idViaje){
                $qa = $this->_pregunta->getPreguntasYRespuestas($idViaje);               
                if ($qa < 0){
                    return null;
                }else{
                    return $qa;
                }
    }
    private function validarDatos($campo) {
        $errors = false;
        if ($campo == "") {            
             return $errors = true;
        }
    }
}
