<?php

class vehiculoModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getVehiculos() {
        $vehiculo = $this->_db->query("select * from vehiculo");
        return $vehiculo->fetchAll();
    }

    public function getVehiculosActivosByUserId($idusuairo) {
        $idusuairo = (int) $idusuairo;
        $sql = "select * from vehiculo where (id_usuario = :idusuario) and (id_estado=1)";
        $params = array(':idusuario' => $idusuairo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVehiculosTodosByUserId($idusuairo) {
        $idusuairo = (int) $idusuairo;
        $sql = "select * from vehiculo where id_usuario = :idusuario";
        $params = array(':idusuario' => $idusuairo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVehiculosInactivosByUserId($idusuairo) {
        $idusuairo = (int) $idusuairo;
        $sql = "select * from vehiculo where (id_usuario = :idusuario)  and (id_estado=2)";
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
        $sql = "select * from vehiculo where id_usuario = :id_usuario and id_estado = 0";
        $params = array(":id_usuario"=> $iduser);
        $this->_db->execute($sql, $params);
        return $this->_db->rowCount();
    }

    public function insertarVehiculo($form, $idUsuario) {
        $sql = "INSERT INTO vehiculo(patente, modelo, marca, id_usuario, asientos, baul, fecha_crea, fecha_modi) 
            VALUES (:patente, :modelo, :marca, :id_usuario, :asientos, :baul, NOW(), NOW())";
        $params = array(":patente"=> $form["patente"], "modelo"=>$form["modelo"], "marca"=>$form["marca"],
                "id_usuario"=>$idUsuario, "asientos"=>$form["asientos"], "baul"=>$form["baul"]);
        $this->_db->execute($sql, $params);
    }
    public function darDeBaja ($id){
        $sql="UPDATE `vehiculo` SET `id_estado`= 2 WHERE id=:idvehiculo";
        $params= array (":idvehiculo"=>$id);
        $this->_db->execute($sql, $params);
    }
    
    public function restoreVehiculo($id){
        $sql="UPDATE `vehiculo` SET `id_estado`= 1 WHERE id=:idvehiculo";
        $params= array (":idvehiculo"=>$id);
        $this->_db->execute($sql, $params);
    }
    
    public function consultarPatente ($form){
       $sql= "SELECT COUNT(*) as cantidad FROM `vehiculo` WHERE (patente=:patente) and (id_usuario=:idusuario)"; 
       $params = array(":patente"=> $form["patente"], ":idusuario" =>Session::get("id_usuario"));
       $this->_db->execute($sql, $params);
       return $this->_db->fetch();
    }

}
?>


