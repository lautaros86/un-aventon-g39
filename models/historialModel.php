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
    public function getViajesRealizados($id){
        (int)$id;
        $sql = "select viaje.fecha, viaje.hora ,viaje.origen, viaje.destino, viaje.monto, usuarios.nombre,usuarios.apellido
        from postulacion 
        inner join estado_postulacion on postulacion.id_estado = estado_postulacion.id 
        inner join viaje on postulacion.id_viaje = viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id 
        where postulacion.id_pasajero = :id and estado_postulacion.id = 2 and viaje.id_estado = 5";
        $params = array(':id' => $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPostulaciones($id) {
        (int) $id;
        $sql = "select viaje.fecha, viaje.hora ,viaje.origen, viaje.destino, viaje.monto,viaje.asientos, usuarios.nombre,usuarios.apellido, usuarios.foto
        from postulacion 
        inner join estado_postulacion on postulacion.id_estado = estado_postulacion.id 
        inner join viaje on postulacion.id_viaje = viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id 
        where estado_postulacion.id = 2 OR estado_postulacion.id = 5 and viaje.id_estado = 1 AND postulacion.id_pasajero = :id";
        $params = array(':id' => $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }
}
