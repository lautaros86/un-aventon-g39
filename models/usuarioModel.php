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
        $sql = "UPDATE postulacion SET id_estado = 4 WHERE id_pasajero = :id_pasajero and id_viaje = :idviaje";
        $params = array(':id_pasajero' => $idUsuario,
            ':idviaje' => $idViaje);
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

    /**
     * 
     * Genera una calificacion negativa para un usuario determinado sin 
     * setear id de viaje ni usuario calificante
     * por lo que se interpreta como calificaciond el sistema.
     * 
     * @param type $idUsuario
     * @param type $valor
     */
    public function calificacionAutomatica($idUsuario, $valor) {
        $sql = "INSERT INTO `calificaciones`( `id_calificado`, `calificacion`) VALUES (:idusuario, :valor)";
        $this->_db->execute($sql, array(
            ':idusuario' => $idUsuario,
            ':valor' => $valor
                )
        );
    }

    /**
     * Retorna todas las calificaciones hechas o por hacer de un usuario
     * @param type $idUsusario
     * @return type
     */
    public function getCalificacionesOf($idUsusario) {
        $sql = "select * 
            from calificaciones 
            where id_calificante = :id_calificante
            order by fecha_crea";
        $params = array(
            ":id_calificante" => $idUsusario
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }

    /**
     * Retorna todas las calificaciones hechas a un usuario
     * @param type $idUsusario
     * @return type
     */
    public function getCalificacionesFor($idUsusario) {
        $sql = "select * 
            from calificaciones 
            where id_calificado = :id_calificado
            order by fecha_crea";
        $params = array(
            ":id_calificado" => $idUsuario
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }

    /**
     * crea una calificacion asociada a un viaje donde el calificante puntua al calificado.
     * @param type $idUsusario
     * @return type
     */
    public function crearCalificacion($idviaje, $calificante = null, $calificado = null) {
        $sql = "insert INTO calificaciones (id_viaje, id_calificante, id_calificado, fecha_crea, fecha_modi)
            VALUES (:id_viaje, :id_calificante, :id_calificado, NOW(), NOW())";
        $params = array(
            ":id_viaje" => $idviaje,
            ":id_calificante" => $calificante,
            ":id_calificado" => $calificado
        );
        $this->_db->execute($sql, $params);
    }

    public function calificar($idCali, $value) {
        $sql = "UPDATE calificaciones SET calificacion = :value WHERE id = :id";
        $this->_db->execute($sql, array(':id' => $idCali, ':value' => $value));
    }

    /**
     * 
     * Actualiza la reputacion para un usuario determinado con el valor recibido.
     * 
     * @param type $idUsuario
     * @param type $valor
     */
    public function actualizarReputacion($idUsuario, $valor) {
        $sql = "UPDATE `usuarios` set reputacion = reputacion + :valor where id = :idusuario";
        $params = array(':idusuario' => $idUsuario,':valor' => $valor);
        $this->_db->execute($sql,$params);
    }

}

?>
