<?php

class viajeModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getViajesPublicos() {
        $sql = "select ev.nombre as estadonombre, v.* 
            from viaje v inner join estado_viaje ev
            on(v.id_estado = ev.id ) 
            where id_estado in (1, 4)";
        $this->_db->execute($sql);
        return $this->_db->fetchAll();
    }

    public function getViaje($id) {
        $sql = "select ev.nombre nombre, v.* from viaje v inner join estado_viaje ev on (v.id_estado = ev.id)
            where v.id = :idviaje";
        $this->_db->execute($sql, array(":idviaje" => $id));
        $viaje = $this->_db->fetch();
        $sql = "select * from viaje_fechas where id_viaje = :idviaje";
        $this->_db->execute($sql, array(":idviaje" => $id));
        $viaje["fechas"] = $this->_db->fetchAll();
        return $viaje;
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
        $sql = "INSERT INTO viaje (monto,duracion,origen,destino,id_chofer,id_vehiculo,asientos,fecha_crea, fecha_modif) 
                VALUES (:monto, :duracion,:origen,:destino,:id_chofer,:id_vehiculo,:asientos, NOW(), NOW())";
        $params = array(
            ":monto" => $form["monto"],
            ":duracion" => $form["duracion"],
            ":origen" => $form["origen"],
            ":destino" => $form["destino"],
            ":id_chofer" => Session::get("id_usuario"),
            ":id_vehiculo" => $form["idVehiculo"],
            ":asientos" => $form["asientos"]
        );
        $this->_db->execute($sql, $params);
        return $this->_db->lastInsertId();
    }
    
    public function setFechas($idviaje, $fecha, $hora) {
        $sql = "INSERT INTO viaje_fechas (id_viaje, fecha, hora) 
                VALUES (:id_viaje, STR_TO_DATE(:fecha, '%d/%m/%Y'), :hora)";
        $params = array(
            ":id_viaje" => $idviaje,
            ":fecha" => $fecha,
            ":hora" => $hora
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
            where id_viaje = :id_viaje and id_estado in (1, 2, 5)";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
        return $this->_db->fetchAll();
    }

    /**
     * 
     * Retorna las los ids de usuarios postulados a un id de viaje.
     * 
     * @param type $idviaje
     * @return type
     */
    public function getViajesPostuladosOf($idUsuario) {
        $sql = "select p.id id_postulacion, ep.nombre as estadopostulacion, v.id as id_viaje, v.* from postulacion p 
            inner join viaje v on(p.id_viaje = v.id) 
            inner join estado_postulacion ep on (p.id_estado = ep.id)
            where p.id_pasajero = :id_pasajero
            and p.id_estado in (1, 2)";
        $this->_db->execute($sql, array(":id_pasajero" => $idUsuario));
        return $this->_db->fetchAll();
    }

    /**
     * 
     * Retorna la cantidad de postuilaciones aceptadas para un viaje.
     * 
     * @param type $idviaje
     * @return type
     */
    public function getPostulacionesAceptadasCant($idviaje) {
        $sql = "select * from postulacion where id_viaje = :id_viaje and id_estado = 2";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
        return $this->_db->rowCount();
    }

    /**
     * 
     * Retorna la cantidad de postuilaciones aceptadas para un viaje.
     * 
     * @param type $idviaje
     * @return type
     */
    public function getPostulacionesAceptadas($idviaje) {
        $sql = "select * from postulacion where id_viaje = :id_viaje and id_estado IN ( 2, 5)";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
        return $this->_db->fetchAll();
    }

    /**
     * 
     * Retorna los pasajeros de un viaje
     * 
     * @param type $idviaje
     * @return type
     */
    public function getPasajeros($idViaje) {
        $sql = "select p.id as id_postulacion, p.id_pasajero, u.*
                from postulacion p
                inner join usuarios u on (p.id_pasajero = u.id)
                where p.id_viaje = :id_viaje and p.id_estado in (2, 5)";
        $this->_db->execute($sql, array(":id_viaje" => $idViaje));
        return $this->_db->fetchAll();
    }

    /**
     * 
     * Retorna las los ids de usuarios postulados a un id de viaje.
     * 
     * @param type $idviaje
     * @return type
     */
    public function getPostulacion($idPostulacion) {
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

    public function finalizarPostulacion($idPostu) {
        $sql = "update postulacion set id_estado = 5 where id = :id";
        $this->_db->execute($sql, array(":id" => $idPostu));
    }

    /**
     * 
     * Dado un datetime y un intervalo de tiempo en segundos, retorna los viajes que se superpongan en ese intervalo de tiempo.
     * 
     * @param type $idPostu
     * @return type
     */
    public function getViajesSupuerpuestos($datetime, $segundos) {
        $sql = "SELECT vf.fecha as fecha, vf.hora as hora, v.*, vf.* FROM `viaje` v inner join viaje_fechas vf on (v.id = vf.id_viaje)
                WHERE
                (
                -- El inicio de mi viaje este entre el inicio y el final de otro viaje
                    (:dateTime BETWEEN DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') + INTERVAL duracion SECOND)
                    OR 
                -- El inicio de otro viaje este entre el principio y fin de mi nuevo viaje
                    (DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') BETWEEN :dateTime AND :dateTime + INTERVAL :segundos SECOND )
                    OR 
                -- El fin de otro viaje este entre el principio y fin de mi nuevo viaje
                    (DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') + INTERVAL duracion SECOND BETWEEN :dateTime AND :dateTime + INTERVAL :segundos SECOND )
                )
                and v.id_chofer = :id_chofer
                and v.id_estado in (1, 2, 4)
                and vf.realizado = 0";
        $chofer = Session::get("id_usuario");
        $this->_db->execute($sql, array(
            ":segundos" => $segundos,
            ":dateTime" => $datetime,
            ":id_chofer" => $chofer
                )
        );
        return $this->_db->fetchAll();
    }

    /**
     * 
     * Dado un datetime, un intervalo de tiempo en segundos y un idvehiculo,, retorna los viajes que se superpongan en ese intervalo de tiempo con ese vehiculo.
     * 
     * @param type $idPostu
     * @return type
     */
    public function getAutosSupuerpuestos($datetime, $segundos, $idVehiculo) {
        $sql = "SELECT vf.fecha as fecha, vf.hora as hora, v.*, vf.* FROM `viaje` v inner join viaje_fechas vf on (v.id = vf.id_viaje)
                WHERE
                (
                -- El inicio de mi viaje este entre el inicio y el final de otro viaje
                    (:dateTime BETWEEN DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') + INTERVAL duracion SECOND)
                    OR 
                -- El inicio de otro viaje este entre el principio y fin de mi nuevo viaje
                    (DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') BETWEEN :dateTime AND :dateTime + INTERVAL :segundos SECOND )
                    OR 
                -- El fin de otro viaje este entre el principio y fin de mi nuevo viaje
                    (DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') + INTERVAL duracion SECOND BETWEEN :dateTime AND :dateTime + INTERVAL :segundos SECOND )
                )
                and v.id_vehiculo = :id_vehiculo
                and v.id_estado in (1, 2, 4)
                and vf.realizado = 0";
        $this->_db->execute($sql, array(
            ":segundos" => $segundos,
            ":dateTime" => $datetime,
            ":id_vehiculo" => $idVehiculo
                )
        );
        return $this->_db->fetchAll();
    }

    /**
     * 
     * Dado un datetime, un intervalo de tiempo en segundos y un usuario,
     *  retorna las postulaciones a viajes que se superpongan en ese intervalo 
     * de tiempo.
     * 
     * @param type $idPostu
     * @return type
     */
    public function getPostulacionesSupuerpuestos($datetime, $segundos, $idPasajero) {
        $sql = "SELECT * FROM `postulacion` p inner join viaje v on (p.id_viaje = v.id)
                inner join viaje_fechas vf on(v.id = vf.id_viaje)
                WHERE
                (
                -- El inicio de mi viaje este entre el inicio y el final de otro viaje
                    (:dateTime BETWEEN DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') + INTERVAL duracion SECOND)
                    OR 
                -- El inicio de otro viaje este entre el principio y fin de mi nuevo viaje
                    (DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') BETWEEN :dateTime AND :dateTime + INTERVAL :segundos SECOND )
                    OR 
                -- El fin de otro viaje este entre el principio y fin de mi nuevo viaje
                    (DATE_FORMAT(concat(vf.fecha, ' ', vf.hora), '%Y-%m-%d %H:%i:%s') + INTERVAL duracion SECOND BETWEEN :dateTime AND :dateTime + INTERVAL :segundos SECOND )
                )
                -- que la postulacion este aceptada
                and p.id_estado = 2
                and p.id_pasajero = :id_pasajero
                and v.id_estado in (1, 2, 4)
                and vf.realizado = 0";
        $this->_db->execute($sql, array(
            ":segundos" => $segundos,
            ":dateTime" => $datetime,
            ":id_pasajero" => $idPasajero
                )
        );
        return $this->_db->fetchAll();
    }

    public function cancelarViaje($idviaje) {
        $sql = "UPDATE viaje SET id_estado = 3 WHERE id = :id_viaje";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
    }

    public function finaizarViaje($idviaje) {
        $sql = "UPDATE viaje SET id_estado = 5 WHERE id = :id_viaje";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
    }

    /**
     * retorna todos los viajes con estado abierto
     * @return type
     */
    public function getViajesAbiertosDe() {
        $sql = "select estado_viaje.nombre as estadonombre, viaje.id, viaje.asientos, viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido, usuarios.foto
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_estado in (1, 4)";
        $this->_db->execute($sql);
        return $this->_db->fetchAll();
    }

    public function getViajesIniciadosDe() {
        $sql = "select estado_viaje.nombre as estadonombre, viaje.id, viaje.asientos, viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido, usuarios.foto
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_estado = 2";
        $this->_db->execute($sql);
        return $this->_db->fetchAll();
    }

    public function getViajesFinalizadosDe() {
        $sql = "select estado_viaje.nombre as estadonombre, viaje.id, viaje.asientos,viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido, usuarios.foto
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_estado = 5";
        $this->_db->execute($sql);
        return $this->_db->fetchAll();
    }

    public function getViajesCanceladosDe() {
        $sql = "select estado_viaje.nombre as estadonombre, viaje.id, viaje.asientos, viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido, usuarios.foto
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_estado = 3";
        $this->_db->execute($sql);
        return $this->_db->fetchAll();
    }

    /**
     * retorna todos los viajes de un chofer
     * @return type
     */
    public function getViajesDe($idUsuario) {
        $sql = "select estado_viaje.nombre as estadonombre, viaje.id, viaje.asientos, viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido, usuarios.foto
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_chofer = :id_chofer";
        $this->_db->execute($sql, array(":id_chofer" => $idUsuario));
        return $this->_db->fetchAll();
    }

    public function buscarViaje($search) {
        $sql = "select viaje.id, viaje.asientos,viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        inner join viaje_fechas on viaje.id = viaje_fechas.id_viaje
        where viaje.id_estado = 1 and viaje_fechas.fecha = :fecha and viaje.origen = :origen and viaje.destino = :destino";
        $params = array(
            ":fecha" => $search["fecha"],
            ":origen" => $search["origen"],
            ":destino" => $search["destino"]
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }

}
?>


