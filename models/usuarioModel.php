<?php

class usuarioModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getUsuarios() {
        $usuario = $this->_db->query("select * from usuarios");
        return $usuario->fetchall();
    }

    public function getUsuario($id) {
        $id = (int) $id;
        $usuario = $this->_db->query("select * from usuarios where id = $id");
        return $usuario->fetch();
    }

    public function insertarUsuario($titulo, $cuerpo) {
        $this->_db->prepare("INSERT INTO usuarios VALUES (null, :titulo, :cuerpo)")
                ->execute(
                        array(
                            ':titulo' => $titulo,
                            ':cuerpo' => $cuerpo
        ));
    }

    public function editarUsuario($id, $nombre, $apellido, $fecha) {        
        $id = (int) $id;
        $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, fecha_nac = :fecha WHERE id = :id";
        $params = array(':id' => $id, ':nombre' => $nombre, ':apellido' => $apellido, ':fecha' => $fecha);
        $this->_db->execute($sql, $params);
    }

    public function eliminarUsuario($id) {
        $id = (int) $id;
        $sql = "UPDATE usuarios SET estado = 2 WHERE id = :id";
        $this->_db->execute($sql, array(':id' => $id));
    }

}

?>
