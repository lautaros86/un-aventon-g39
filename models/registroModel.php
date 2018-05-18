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
        $sql ="insert into usuarios values (null, :nombre, :apellido, :email, :fecha_nac, :password, NOW(), NOW())";
        $date = date($fecha_nac);
        $this->_db->execute($sql, array(
                    ':nombre' => $nombre,
                    ':apellido' => $apellido,
                    ':email' => $email,
                    ':fecha_nac' => $date,
                    ':password' => Hash::getHash('sha256', $password, HASH_KEY)
        ));
    }

}

?>
