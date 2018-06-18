<?php

abstract class Notificacion {
    
    
    
    static public function getNotificaciones() {
        $db =  new DatabasePDO();
        $sql = "select * from notificacion where idusuario = :idusuario and estado = 1";
        $params = array(":idusuario" => Session::get("id_usuario"));
        $db->execute($sql, $params);
        return $db->fetchAll();
    }


}

?>