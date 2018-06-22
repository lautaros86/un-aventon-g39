<?php

class registroModel extends Model {

    public function __construc() {
        parent::__construc();
    }

    public function verificarUsuario($usuario) {
        $sql = "select id from usuarios where usuario = :usuario";
        $params = array(":usuario" => $usuario);
        $this->_db->execute($sql, $params);
        if ($this->_db->fetchAll(PDO::FETCH_ASSOC)) {
            return true;
        }
        return false;
    }

    public function verificarEmail($email) {
        $sql = "select id from usuarios where email = :email";
        $params = array(":email" => $email);
        $this->_db->execute($sql, $params);
        if (sizeof($this->_db->fetchAll(PDO::FETCH_ASSOC)) > 0) {
            return true;
        }
        return false;
    }

    public function registrarUsuario($nombre, $apellido, $email, $fecha_nac, $password) {
        $sql ="insert into usuarios (nombre, apellido, email, fecha_nac, password, fecha_crea, fecha_modif)
            values (:nombre, :apellido, :email, STR_TO_DATE(:fecha_nac, '%d/%m/%Y'), :password, NOW(), NOW())";
        $this->_db->execute($sql, array(
                    ':nombre' => $nombre,
                    ':apellido' => $apellido,
                    ':email' => $email,
                    ':fecha_nac' => $fecha_nac,
                    ':password' => Hash::getHash('sha256', $password, HASH_KEY)
        ));
    }

}

?>
