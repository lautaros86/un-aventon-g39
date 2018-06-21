<?php

class viajeModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getViajes() {
        $viajes = $this->_db->query("select * from viajes");
        return $viajes->fetchAll();
    }

    public function getViaje($id) {
        $sql = "select * from viaje where id = :idviaje";
        $this->_db->execute($sql, array(":idviaje" => $id));
        return $this->_db->fetch();
    }

    /**
     * Retorna la cantidad de viajes de un usuario.
     * @param type $id_chofer id de usuaario
     * @return type
     */
    public function getCantViajesChofer($id_chofer) {
        $sql = "select * from viaje where id_chofer = :id_chofer";
        $this->_db->execute($sql, array(":id_chofer" => $id_chofer));
        return $this->_db->rowCount();
    }

    // TODO: deberia traer la cantidad de viajes como pasajero.
    public function getCantViajesPasajero($id_chofer) {
        $sql = "select * from viaje where id_chofer = :id_chofer";
        $this->_db->execute($sql, array(":id_chofer" => $id_chofer));
        return $this->_db->rowCount();
    }

    public function insertarViaje($form) {
        $sql = "INSERT INTO viaje (monto,fecha,hora,origen,destino,id_chofer,id_vehiculo,asientos,fecha_crea, fecha_modif) 
                VALUES (:monto,STR_TO_DATE(:fecha, '%d/%m/%Y'),:hora,:origen,:destino,:id_chofer,:id_vehiculo,:asientos, NOW(), NOW())";
        $params = array(
            ":monto" => $form["monto"],
            ":fecha" => $form["fecha"],
            ":hora" => $form["hora"],
            ":origen" => $form["origen"],
            ":destino" => $form["destino"],
            ":id_chofer" => Session::get("id_usuario"),
            ":id_vehiculo" => $form["idVehiculo"],
            ":asientos" => $form["asientos"]
        );
        $this->_db->execute($sql, $params);
    }

    /**
     * 
     * Retorna las los ids de usuarios postulados a un id de viaje.
     * 
     * @param type $idviaje
     * @return type
     */
    public function getPostulacionesViaje($idviaje) {
        $sql = "select p.id id_postulacion, p.id_pasajero, p.id_viaje, p.id_estado estado_postulacion, u.* from postulacion p 
            inner join usuarios u on(p.id_pasajero = u.id)            
            where id_viaje = :id_viaje and id_estado in (1, 2)";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
        return $this->_db->fetchAll();
    }

    /**
     * 
     * Retorna la cantidad de postuilaciones aceptadas para un viaje.
     * 
     * @param type $idviaje
     * @return type
     */
    public function getPostulacionesAceptadas($idviaje) {
        $sql = "select * from postulacion where id_viaje = :id_viaje and id_estado = 2";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
        return $this->_db->rowCount();
    }

    /**
     * 
     * Retorna la cantidad de viajes que hay superpuestos para una postulacion.
     * 
     * @param string $fecha formato "YYYY-DD-MM"
     * @param string $horaInicio formato "HH:MM:SS"
     * @param string $horaFin formato "HH:MM:SS"
     * @param string $idviaje (optional)
     */
    public function validarSuperposicionDeViajesConPostulaciones($idPasajero, $fecha, $horaInicio, $horaFin, $idPostulacion, $idviaje = 0) {
        $sql = "select * 
                from postulacion
                inner join viaje on postulacion.id_viaje = viaje.id
                where id_pasajero = :id_pasajero
                and postulacion.id_estado = 2
                and viaje.id_estado in (1, 2, 4)
                and fecha = :fecha 
                AND hora BETWEEN :hora_ini AND :hora_fin
                AND viaje.id <> :id_viaje;";
        $this->_db->execute($sql, array(
            ":id_viaje" => $idviaje,
            ":id_pasajero" => $idPasajero,
            ":fecha" => $fecha,
            ":hora_ini" => $horaInicio,
            ":hora_fin" => $horaFin,
        ));
        return $this->_db->rowCount();
    }

    /**
     * 
     * Retorna las los ids de usuarios postulados a un id de viaje.
     * 
     * @param type $idviaje
     * @return type
     */
    public function getPostulacion($idPostulacion) {
//        $sql = "select p.id id_postulacion, p.id_pasajero, p.id_viaje, p.id_estado estado_postulacion, u.*, v.* from postulacion p 
//            inner join usuarios u on(p.id_pasajero = u.id) 
//            inner join viaje v on (p.id_viaje = v.id)
//            where p.id = :id_postu";
        $sql = "select * from postulacion where id = :id_postu";
        $this->_db->execute($sql, array(":id_postu" => $idPostulacion));
        return $this->_db->fetch();
    }

    /**
     * 
     * Cambia el estado de una postulacion a aceptado(2).
     * 
     * @param type $idPostu
     * @return type
     */
    public function aceptarPostulacion($idPostu) {
        $sql = "update postulacion set id_estado = 2 where id = :id";
        $this->_db->execute($sql, array(":id" => $idPostu));
    }

    /**
     * 
     * Cambia el estado de una postulacion a rechazado(3).
     * 
     * @param type $idPostu
     * @return type
     */
    public function rechazarPostulacion($idPostu) {
        $sql = "update postulacion set id_estado = 3 where id = :id";
        $this->_db->execute($sql, array(":id" => $idPostu));
    }

    public function cancelarViaje($idviaje) {
        $sql = "UPDATE viaje SET id_estado = 3 WHERE id = :id_viaje";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
    }

    /**
     * retorna todos los viajes con estado abierto
     * @return type
     */
    public function getViajesAbiertos() {
        $sql = "select viaje.id, viaje.asientos,viaje.fecha, viaje.hora, viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_estado = 1";
        $this->_db->execute($sql);
        return $this->_db->fetchAll();
    }

}
?>


