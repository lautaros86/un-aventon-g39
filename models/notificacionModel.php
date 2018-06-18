<?php

class notificacionModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    public function getNotificaciones($idPersona) {
        $sql = "select * from notificacion where idusuario = :idusuario order by estado, fecha limit 11";
        $params = array(":idusuario" => $idPersona);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }

    public function crearNotificacion($mensaje, $destinatarios, $color) {
        $this->_db->prepare("INSERT INTO notificacion(mensaje, color, idusuario, fecha) VALUES (:mensaje, :color, :idusuario, now())");
        $fallo = false;
        $this->_db->bindValue(':mensaje', $mensaje);
        $this->_db->bindValue(':color', $color);
        foreach ($destinatarios as $idDestinatario) {
            $this->_db->bindValue(':idusuario', $idDestinatario);
            if (!$this->_db->bindExecute())
                $fallo = true;
        }
        return $fallo;
    }

    public function limpiarNotificaciones($idPersona, $idsNotificaciones) {
        $this->_db->prepare("update notificacion set estado = 2 where idusuario = :idusuario and id=:id");
        $fallo = false;
        foreach ($idsNotificaciones as $id) {
            $this->_db->bindValue(':idusuario', $idPersona);
            $this->_db->bindValue(':id', $id);
            if (!$this->_db->bindExecute())
                $fallo = true;
        }
        return $fallo;
    }

}
?>


