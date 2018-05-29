<?php

class vehiculoModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getVehiculos() {
        $vehiculo = $this->_db->query("select * from vehiculo");
        return $usuario->fetchall();
    }

    public function getVehiculo($id) {
        $id = (int) $id;
        $vehiculo = $this->_db->query("select * from vehiculo where id = $id");
        return $vehiculo->fetch();
    }

    public function insertarVehiculo($titulo, $cuerpo) {
        $this->_db->prepare("INSERT INTO usuarios VALUES (null, :titulo, :cuerpo)")
                ->execute(
                        array(
                            ':titulo' => $titulo,
                            ':cuerpo' => $cuerpo
        ));
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
        $sql = "select * from vehiculo where id_usuario = :idusuario";
        $params = array(':idusuario' => $idusuairo);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchall(PDO::FETCH_ASSOC);
    }

}
?>


