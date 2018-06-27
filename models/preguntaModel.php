<?php

class preguntaModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getPregunta() {
        $sql = "select * from pregunta where id = :id";
        $params = array(
            ":id" => $id
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
    }

    public function getRespuesta() {
        $sql = "select * from pregunta where id = :id";
        $params = array(
            ":id" => $id
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
    }

    public function eliminarPregunta() {
        $sql = "UPDATE pregunta SET estado = 0, WHERE id = :id";
        $params = array(":id" => $id);
        $this->_db->execute($sql, $params);
    }

    public function responder($param) {
        $sql = "INSERT INTO respuesta (id_pregunta, mensaje, fecha_crea) 
                VALUES (:id_pregunta, :mensaje, NOW())";
        $params = array(
            ":id_pregunta" => $param['id_pregunta'],
            ":mensaje" => $param['mensaje'],
        );
        $this->_db->execute($sql, $params);
    }

    public function preguntar($param) {
        $sql = "INSERT INTO preguntas (estado, mensaje, id_requester, id_viaje, fecha_crea) 
                VALUES (:estado, :mensaje, :id_requester, :id_viaje, NOW())";
        $params = array(
            ":estado" => $param['estado'],
            ":mensaje" => $param['mensaje'],
            ":id_requester" => $param['id_requester'],
            ":id_viaje" => $param['id_viaje'],
        );
        $this->_db->execute($sql, $params);
    }

}
