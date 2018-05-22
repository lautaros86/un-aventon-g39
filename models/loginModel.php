<?php

class loginModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getUsuario($mail, $password) {
        $sql =  "select * from usuarios where email = :email and pass = :password and estado in (1, 3)";
        $params = array(":email" => $email, ':password' => Hash::getHash('sha256', $password, HASH_KEY));
        $this->_db->execute($sql, $params);
        return  $this->_db->fetch(PDO::FETCH_ASSOC);
    }

}
?>

