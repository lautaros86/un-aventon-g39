<?php

class viajeModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getViajes() {
        $vehiculo = $this->_db->query("select * from viajes");
        return $vehiculo->fetchAll();
    }

    public function getViaje($id) {
        $id = (int) $id;
        $vehiculo = $this->_db->query("select * from viajes where id = $id");
        return $vehiculo->fetch();
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


