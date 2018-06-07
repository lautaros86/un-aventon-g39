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
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $user = $this->_db->execute($sql, array(':id' => $id)); 
        return $this->_db->fetch(PDO::FETCH_ASSOC);
    }

    public function insertarUsuario($titulo, $cuerpo) {
        $this->_db->prepare("INSERT INTO usuarios VALUES (null, :titulo, :cuerpo)")
                ->execute(
                        array(
                            ':titulo' => $titulo,
                            ':cuerpo' => $cuerpo
        ));
    }

    public function editarUsuario($datos) {        
        $id = (int) $id;
        $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, fecha_nac = :fecha";
        if(isset($foto) && $foto !== ""){
            $sql .= ", foto = :foto ";
            $params = array(':foto' => $datos["foto"]);
        }
        $sql .= " WHERE id = :id";
        $params = array(':id' => $datos["id"], ':nombre' => $datos["nombre"], ':apellido' => $datos["apellido"], ':fecha' => $datos["fecha"]);
        $this->_db->execute($sql, $params);
    }

    public function eliminarUsuario($id) {
        $id = (int) $id;
        $sql = "UPDATE usuarios SET estado = 2 WHERE id = :id";
        $this->_db->execute($sql, array(':id' => $id));
    }
    public function editarUsuarioContrasenia($id, $pass) {        
        $id = (int) $id;
        $sql = "UPDATE usuarios SET password = :pass WHERE id = :id";
        $params = array(':id' => $id, ':pass' => Hash::getHash('sha256', $pass, HASH_KEY));
        $this->_db->execute($sql, $params);
    }
}

?>
