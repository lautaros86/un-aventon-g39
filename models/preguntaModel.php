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

    public function getPreguntasYRespuestas($idViaje) {
        $sql = "SELECT 
            respuesta.id as idRespuesta,
            respuesta.id_pregunta,
            respuesta.mensaje as respuesta,
            respuesta.fecha_crea as fechaRespuesta,
            preguntas.id as idPregunta,
            preguntas.estado,
            preguntas.mensaje as pregunta,
            preguntas.id_requester,
            preguntas.id_viaje,
            preguntas.fecha_crea as fechaPregunta
            FROM preguntas 
            INNER JOIN respuesta ON respuesta.id_pregunta = preguntas.id
            WHERE preguntas.id_viaje = :id";
            $params = array(":id" => $idViaje);
            $this->_db->execute($sql, $params);
            return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarPregunta($idPregunta) {
        $sql = "UPDATE pregunta SET estado = 0, WHERE id = :id";
        $params = array(":id" => $idPregunta);
        $this->_db->execute($sql, $params);
    }

    public function responder($param) {
        $sql = "INSERT INTO respuesta (id_pregunta, mensaje, fecha_crea) 
                VALUES (:id_pregunta, :mensaje, NOW())";
        $params = array(
            ":id_pregunta" => $param['id_pregunta'],
            ":mensaje" => $param['respuesta'],
        );
        $this->_db->execute($sql, $params);
    }

    public function preguntar($param) {
        $sql = "INSERT INTO preguntas (estado, mensaje, id_requester, id_viaje, fecha_crea) 
                VALUES (1, :mensaje, :id_requester, :id_viaje, NOW())";
        $params = array(
            ":mensaje" => $param['pregunta'],
            ":id_requester" => $param['idRequester'],
            ":id_viaje" => $param['idViaje'],
        );
        $this->_db->execute($sql, $params);
    }

}
