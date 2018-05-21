<?php

class generarSesionModel extends Model {

    public function __construc() {
        parent::__construc();
    }

    public function obtenerUsuario($usuario, $password) {
        $sql = "select * from usuarios where usuario = :usuario and password = :password";
        $params = array(":usuario" => $usuario, ":password" => $password);
        $this->_db->execute($sql, $params);
        if ($this->_db->fetchAll(PDO::FETCH_ASSOC)) {
            return true;
        }
        return false;
    }

}

?>
