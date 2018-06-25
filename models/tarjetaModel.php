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
        $sql = "select * from tarjeta where id_usuario = :id_usuario ";
        $params = array(":id_usuario" => $id);
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
        return $this->_db->fetchAll();
    }

    //terminar de modificar
    public function insertarTarjeta($form, $idUsuario) {
        // VALUES 
        $sql = "INSERT INTO tarjeta (numero, nombre, vto_mes, vto_anio, id_usuario) 
            VALUES ('4594439577977162', 'visa', '03', '19', '10')";
        $params = array(":patente" => $form["patente"], "modelo" => $form["modelo"], "marca" => $form["marca"],
            "id_usuario" => $idUsuario, "asientos" => $form["asientos"], "baul" => $form["baul"]);
        $this->_db->execute($sql, $params);
    }

    //terminar de modificar
    public function darDeBajaTarjeta($id) {
        $sql = "UPDATE `vehiculo` SET `estado`= 1 WHERE id=:idvehiculo";
        $params = array(":idvehiculo" => $id);
        $this->_db->execute($sql, $params);
        //return $this->db->fetch();
    }

    //terminar de modificar
    public function consultarTarjeta($form) {
        //patente=:patente
        $sql = "SELECT COUNT(*) as cantidad FROM `vehiculo` WHERE (patente=:patente) and (id_usuario=:idusuario)";
        //id_usuario=:idusuario
        $params = array(":patente" => $form["patente"], ":idusuario" => Session::get("id_usuario"));

        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
    }

    //terminar de modificar
    public function eliminarTarjeta($id) {
        $id = (int) $id;
        $sql = "UPDATE usuarios SET estado = 2 WHERE id = :id";
        $this->_db->execute($sql, array(':id' => $id));
    }

}
