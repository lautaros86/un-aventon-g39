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
        $sql = "select * from postulacion where id_viaje = :id_viaje and id_estado in (1, 2)";
        $this->_db->execute($sql, array(":id_viaje" => $idviaje));
        return $this->_db->fetchAll();
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
        $sql = "select viaje.id, viaje.asientos,viaje.fecha, viaje.hora, viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido, usuarios.foto
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_estado = 1";
        $this->_db->execute($sql);
        return $this->_db->fetchAll();
    }
    
    public function buscarViaje($search) {
        $sql="select viaje.id, viaje.asientos,viaje.fecha, viaje.hora, viaje.origen, viaje.destino, viaje.monto, usuarios.nombre, usuarios.apellido
        from viaje 
        inner join estado_viaje on viaje.id_estado = estado_viaje.id
        inner join usuarios on viaje.id_chofer = usuarios.id
        where viaje.id_estado = 1 and viaje.fecha = :fecha and viaje.origen = :origen and viaje.destino = :destino";
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


