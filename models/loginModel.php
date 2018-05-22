<?php

class loginModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getUsuario($email, $password) {
        $sql =  "select * from usuarios where email = :email and password = :password";
        $params = array(":email" => $email, ':password' => Hash::getHash('sha256', $password, HASH_KEY));
        $this->_db->execute($sql, $params);
        return  $this->_db->fetch(PDO::FETCH_ASSOC);
    }

}
?>

