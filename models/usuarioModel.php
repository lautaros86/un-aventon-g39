<?php

class usuarioModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getUsuarios() {
        $usuario = $this->_db->query("select * from usuarios");
        return $usuario->fetchAll();
    }

    public function getUsuario($id) {
        $id = (int) $id;
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $user = $this->_db->execute($sql, array(':id' => $id));
        return $this->_db->fetch(PDO::FETCH_ASSOC);
    }

    public function getDatosChofer($id) {
        $id = (int) $id;
        $sql = "SELECT * 
            FROM usuarios
            inner join
            WHERE id = :id";
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

    public function postular($idUsuario, $idViaje) {
        $sql = "INSERT INTO postulacion (id_pasajero, id_viaje) VALUES (:idusuario, :idviaje)";
        $this->_db->execute($sql, array(
            ':idusuario' => $idUsuario,
            ':idviaje' => $idViaje)
        );
    }
    public function cancelarPostulacion($idUsuario, $idViaje) {
        $id = (int) $id;
        $sql = "UPDATE postulacion SET id_estado = 4 WHERE id_pasajero = :id_pasajero";
        $params = array(':id_pasajero' => $idUsuario);
        $this->_db->execute($sql, $params);
    }
    
    public function editarUsuario($datos) {
        $id = (int) $datos["id"];
        $params = array();
        $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido";
        if (isset($datos["fecha"]) && $datos["fecha"] !== null) {
            $sql .= ", fecha_nac = :fecha ";
            $params = array_merge($params, array(':fecha' => $datos["fecha"]));
        }
        if (isset($datos["foto"]) && $datos["foto"] !== null) {
            $sql .= ", foto = :foto ";
            $params = array_merge($params, array(':foto' => $datos["foto"]));
        }
        $sql .= " WHERE id = :id";
        $params = array_merge($params, array(':id' => $datos["id"], ':nombre' => $datos["nombre"], ':apellido' => $datos["apellido"]));
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
