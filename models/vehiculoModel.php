<?php

class vehiculoModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getVehiculos() {
        $vehiculo = $this->_db->query("select * from vehiculo ");
        return $vehiculo->fetchAll();
    }

    public function getVehiculosOfUser($iduser) {
        $sql = "select * from vehiculo where id_usuario = :id_usuario";
        $params = array(":id_usuario"=> $iduser);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }

    public function cantVehiculos($iduser) {
        $sql = "select * from vehiculo where id_usuario = :id_usuario and estado = 0";
        $params = array(":id_usuario"=> $iduser);
        $this->_db->execute($sql, $params);
        return $this->_db->rowCount();
    }

    public function getVehiculo($id) {
        $id = (int) $id;
        $vehiculo = $this->_db->query("select * from vehiculo where id = $id");
        return $vehiculo->fetch();
    }

    public function insertarVehiculo($form, $idUsuario) {
        $sql = "INSERT INTO vehiculo(patente, modelo, marca, id_usuario, asientos, baul) "
                . "VALUES (:patente, :modelo, :marca, :id_usuario, :asientos, :baul)";
        $params = array(":patente"=> $form["patente"], "modelo"=>$form["modelo"], "marca"=>$form["marca"],
                "id_usuario"=>$idUsuario, "asientos"=>$form["asientos"], "baul"=>$form["baul"]);
        $this->_db->execute($sql, $params);
    }
    public function darDeBaja ($id){
        $sql="UPDATE `vehiculo` SET `estado`= 1 WHERE id=:idvehiculo";
        $params= array (":idvehiculo"=>$id);
        $this->_db->execute($sql, $params);
        //return $this->db->fetch();
    }
    
    public function consultarPatente ($form){
        //patente=:patente
       $sql= "SELECT COUNT(*) as cantidad FROM `vehiculo` WHERE (patente=:patente) and (id_usuario=:idusuario)"; 
        //id_usuario=:idusuario
       $params = array(":patente"=> $form["patente"], ":idusuario" =>Session::get("id_usuario"));
       
       $this->_db->execute($sql, $params);
       return $this->_db->fetch();
    }
    public function editarUsuario($id, $titulo, $cuerpo) {
        $id = (int) $id;

        $this->_db->prepare("UPDATE usuarios SET titulo = :titulo, cuerpo = :cuerpo WHERE id = :id")
                ->execute(
                        array(
                            ':id' => $id,
                            ':titulo' => $titulo,
                            ':cuerpo' => $cuerpo
        ));
    }

    public function eliminarUsuario($id) {
        $id = (int) $id;
        $sql = "UPDATE usuarios SET estado = 2 WHERE id = :id";
        $this->_db->execute($sql, array(':id' => $id));
    }
  

    public function getVehiculosByUserId($idusuairo) {
        $idusuairo = (int) $idusuairo;
        $sql = "select * from vehiculo where (id_usuario = :idusuario) and (estado=0)";
        $params = array(':idusuario' => $idusuairo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>


