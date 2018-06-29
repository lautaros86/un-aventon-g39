<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class historialModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    //esta consulta trae de la base de datos todos los viajes del pasajero en lo cuales haya sido aceptado y el viaje este como finalizado
    public function getViajesRealizados($id) {
        (int) $id;
        $sql = "select viaje_fechas.fecha, viaje_fechas.hora ,viaje.origen, viaje.destino, viaje.monto, usuarios.nombre,usuarios.apellido
        from postulacion 
        inner join estado_postulacion on postulacion.id_estado = estado_postulacion.id 
        inner join viaje on postulacion.id_viaje = viaje.id
        inner join viaje_fechas on viaje.id = viaje_fechas.id_viaje
        inner join usuarios on viaje.id_chofer = usuarios.id 
        where postulacion.id_pasajero = :id and estado_postulacion.id = 2 and viaje.id_estado = 5";
        $params = array(':id' => $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostulaciones($id) {
        (int) $id;
        $sql = "select viaje_fechas.fecha, viaje_fechas.hora ,viaje.origen, viaje.destino, viaje.monto,viaje.asientos, usuarios.nombre,usuarios.apellido, usuarios.id ,usuarios.foto
        from postulacion 
        inner join estado_postulacion on postulacion.id_estado = estado_postulacion.id 
        inner join viaje on postulacion.id_viaje = viaje.id
        inner join viaje_fechas on viaje.id = viaje_fechas.id_viaje
        inner join usuarios on viaje.id_chofer = usuarios.id 
        where estado_postulacion.id = 2 OR estado_postulacion.id = 5 and viaje.id_estado = 1 AND postulacion.id_pasajero = :id";
        $params = array(':id' => $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * retorna todos los viajes donde me postulé sin importar si fui aceptado o rechazado y sin importar el 
     * estado del viaje
     * @param integer $id
     * @return type
     */
    public function getViajesPostulados($id) {
        (int) $id;
        $sql = "select viaje_fechas.fecha, viaje_fechas.hora ,viaje.origen, viaje.destino, viaje.monto, estado_viaje.nombre as nombreEstadoViaje ,estado_postulacion.nombre as nombreEstadoPostulacion ,usuarios.nombre,usuarios.apellido, usuarios.id
        from postulacion 
        inner join estado_postulacion on postulacion.id_estado = estado_postulacion.id 
        inner join viaje on postulacion.id_viaje = viaje.id
        inner join viaje_fechas on viaje.id = viaje_fechas.id_viaje
        inner join estado_viaje on estado_viaje.id = viaje.id_estado
        inner join usuarios on viaje.id_chofer = usuarios.id 
        where postulacion.id_pasajero = :id";
        $params = array(':id' => $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

}
