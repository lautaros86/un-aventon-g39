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
        $sql = "select * 
                from viaje v
                inner join usuarios u on (v.id_chofer = u.id)
                where v.id = :idviaje";
        $this->_db->execute($sql, array(":idviaje" => $id));
        return $this->_db->fetch();
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

}
?>


