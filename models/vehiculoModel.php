<?php

class vehiculoModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getVehiculos() {
        $vehiculo = $this->_db->query("select * from vehiculo");
        return $vehiculo->fetchAll();
    }

    public function getVehiculosById($idVehiculo) {
        $sql = "select * from vehiculo where id = :id";
        $params = array(':id' => $idVehiculo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetch(PDO::FETCH_ASSOC);
    }

    public function getViajesByVehiculoId($idVehiculo) {
        $sql = "select * from viaje where id_vehiculo = :id_vehiculo and id_estado = 1";
        $params = array(':id_vehiculo' => $idVehiculo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVehiculosActivosByUserId($idusuairo) {
        $idusuairo = (int) $idusuairo;
        $sql = "select * from usuario_vehiculo uv inner join vehiculo v on(uv.id_vehiculo = v.id) 
            where (id_usuario = :idusuario) and (uv.id_estado=1)";
        $params = array(':idusuario' => $idusuairo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVehiculosTodosByUserId($idusuairo) {
        $idusuairo = (int) $idusuairo;
        $sql = "select * from usuario_vehiculo uv inner join vehiculo v on(uv.id_vehiculo = v.id) 
            where (id_usuario = :idusuario)";
        $params = array(':idusuario' => $idusuairo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVehiculosInactivosByUserId($idusuairo) {
        $idusuairo = (int) $idusuairo;
        $sql = "select * from usuario_vehiculo uv inner join vehiculo v on(uv.id_vehiculo = v.id) 
            where (id_usuario = :idusuario)  and (id_estado=2)";
        $params = array(':idusuario' => $idusuairo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna la cantidad de vehiculos asociados a un usuario que esten activos
     * @param type $iduser
     * @return type
     */
    public function cantVehiculos($iduser) {
        $sql = "select * from usuario_vehiculo uv inner join vehiculo v on(uv.id_vehiculo = v.id)
            where uv.id_usuario = :id_usuario and uv.id_estado = 1";
        $params = array(":id_usuario"=> $iduser);
        $this->_db->execute($sql, $params);
        return $this->_db->rowCount();
    }

    public function insertarVehiculo($form, $idUser) {
        $sql = "INSERT INTO vehiculo(patente, modelo, marca, asientos, baul, fecha_crea, fecha_modi) 
            VALUES (:patente, :modelo, :marca, :asientos, :baul, NOW(), NOW())";
        $params = array(":patente"=> $form["patente"], "modelo"=>$form["modelo"], "marca"=>$form["marca"],
                 "asientos"=>$form["asientos"], "baul"=>$form["baul"]);
        $this->_db->execute($sql, $params);
        $idVehiculo = $this->_db->lastInsertId();
        $sql = "INSERT INTO usuario_vehiculo(id_usuario, id_vehiculo, fecha_crea, fecha_modi) 
            VALUES (:id_usuario, :id_vehiculo, NOW(), NOW())";
        $params = array(":id_usuario"=> $idUser, "id_vehiculo"=>$idVehiculo);
        $this->_db->execute($sql, $params);
    }
    
    public function darDeBaja ($idVehiculo){
        $sql="UPDATE usuario_vehiculo SET `id_estado`= 2 WHERE id_vehiculo = :idvehiculo and id_usuario = :id_usuario";
        $params= array (":idvehiculo"=>$idVehiculo, ":id_usuario" => Session::get("id_usuario"));
        $this->_db->execute($sql, $params);
    }
    
    public function restoreVehiculo($id){
        $sql="UPDATE usuario_vehiculo SET `id_estado`= 1 WHERE id_vehiculo = :idvehiculo and id_usuario = :id_usuario";
        $params= array (":idvehiculo"=>$idVehiculo, ":id_usuario" => Session::get("id_usuario"));
        $this->_db->execute($sql, $params);
    }
    
    public function consultarPatente ($form){
       $sql= "SELECT COUNT(*) as cantidad FROM vehiculo v inner join usuario_vehiculo uv on (v.id = uv.id_vehiculo)WHERE (v.patente=:patente) and (uv.id_usuario=:idusuario)"; 
       $params = array(":patente"=> $form["patente"], ":idusuario" =>Session::get("id_usuario"));
       $this->_db->execute($sql, $params);
       return $this->_db->fetch();
    }

}
?>


