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

}

?>
