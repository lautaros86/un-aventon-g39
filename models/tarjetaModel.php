<?php

class tarjetaModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * recibo el id del usuario y retorno todas sus tarjetas de crediro 
     * @param type $id
     * @return array
     */
    public function getTarjetasDeUnUsuario($id) {
        $sql = "select tarjeta.id,tarjeta.numero, tarjeta.nombre, tarjeta.vto_mes, tarjeta.vto_anio from usuario_tarjeta 
        inner join tarjeta on usuario_tarjeta.id_tarjeta = tarjeta.id 
        inner join usuarios on usuario_tarjeta.id_usuario = usuarios.id
        where usuario_tarjeta.estado = 1 and usuarios.id = :id";
        $params = array(":id" => $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }

    /**
     * retorna con el id de un usuario todas sus tarjetas de credito
     * @param type $id
     * @return array
     */
    public function getTarjetaDeUnUsuario($id) {
        $sql = "select * from tarjeta where id_usuario = :id";
        $params = array(":id" => $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
    }

    //terminar de modificar
    public function insertarTarjeta($form, $idUsuario) {
        // VALUES 
        $sql = "INSERT INTO tarjeta VALUES (NULL,:numero, :nombre, :nombreBanco ,:vtoMes, :vtoAnio, :idUser, 1)";

        $params = array(":numero" => $form["numero"],
            ":nombre" => $form["nombre"],
            ":vtoMes" => $form["mesVencimiento"],
            ":idUser" => $idUsuario,
            ":vtoAnio" => $form["anioVencimiento"],
            ":nombreBanco" => $form["entidad"]);
        $this->_db->execute($sql, $params);
        $idTarjeta = $this->_db->lastInsertId();
        $sql = "INSERT INTO usuario_tarjeta(id_usuario, id_tarjeta) 
            VALUES (:id_usuario, :id_tarjeta)";
        $params = array(":id_usuario" => $idUsuario, "id_tarjeta" => $idTarjeta);
        $this->_db->execute($sql, $params);
    }

    //terminar de modificar
    public function darDeBajaTarjeta($idTarjeta, $idUsuario) {
        (int) $idTarjeta;
        $sql = "UPDATE usuario_tarjeta SET estado = 0 WHERE id_tarjeta = :id_tarjeta and id_usuario = :id_usuario";
        $params = array(":id_tarjeta" => $idTarjeta, ":id_usuario" => $idUsuario);
        $this->_db->execute($sql, $params);
    }

    /**
     * consulta por numero de tarjeta recibe el numero de tarjeta y el id de un usuario y verifica si el usuario ya tiene esa tarjeta
     */
    public function consultarPorRepetido($numeroTarjeta, $idUsuario) {

        $sql = "SELECT COUNT(*) as cantidad FROM tarjeta WHERE (numero=:numero) and (id_usuario=:idUsuario)";

        $params = array(":numero" => $numeroTarjeta, ":idUsuario" => $idUsuario);

        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
    }

    /**
     * da de baja una tarjeta con su numero de tarjeta como parametro
     */
    //terminar de modificar
    public function eliminarTarjeta($id) {
        (int) $id;
        $sql = "UPDATE usuarios SET estado = 2 WHERE id = :id";
        $this->_db->execute($sql, array(':id' => $id));
    }

}
