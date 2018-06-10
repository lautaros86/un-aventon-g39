<?php

class viajeModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getViajes() {
        $vehiculo = $this->_db->query("select * from viajes");
        return $vehiculo->fetchall();
    }

    public function getViaje($id) {
        $id = (int) $id;
        $vehiculo = $this->_db->query("select * from viajes where id = $id");
        return $vehiculo->fetch();
    }

    public function insertarViaje($form) {
        $sql = "INSERT INTO viaje (monto,fecha,hora,dir_origen,dir_destino,id_localidad_origen,id_localidad_destino,id_chofer,id_vehiculo,asientos) 
                VALUES (:monto,:fecha,:hora,:dir_origen,:dir_destino,:id_localidad_origen,:id_localidad_destino,:id_chofer,:id_vehiculo,:asientos)";
        $params = array(":monto" => $form["monto"],
                        ":fecha" => $form["fecha"],
                        ":hora" => $form["hora"],
                        ":dir_origen" => $form["dir_origen"],
                        ":dir_destino" => $form["dir_destino"],
                        ":id_localidad_origen" => $form["id_localidad_origen"],
                        ":id_localidad_destino" => $form["id_localidad_destino"],
                        ":id_chofer" => Session::get("id_usuario"),
                        ":id_vehiculo" => $form["id_vehiculo"],
                        ":asientos" => $form["asientos"]
                    );
        $this->_db->execute($sql, $params);
    }

}
?>


