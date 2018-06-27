<?php

class walletModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function extraer($idUsuario, $monto) {
        $sql = "update wallet set saldo = saldo - :monto where id_usuario = :id_usuario";
        $params = array(
            ":id_usuario" => $idUsuario,
            ":monto" => $monto
        );
        $this->_db->execute($sql, $params);
    }

    public function depositar($idUsuario, $monto) {
        $sql = "update wallet set saldo = saldo + :monto where id_usuario = :id_usuario";
        $params = array(
            ":id_usuario" => $idUsuario,
            ":monto" => $monto
        );
        $this->_db->execute($sql, $params);
    }

    public function getSaldo($idUsuario) {
        $sql = "select saldo from wallet where id_usuario = :id_usuario";
        $params = array(
            ":id_usuario" => $idUsuario
        );
        $this->_db->execute($sql, $params);
        $result = $this->_db->fetch();
        return $result["saldo"];
    }

}
?>


