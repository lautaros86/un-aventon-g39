<?php

class calificacionModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Retorna todas las calificaciones hechas o por hacer de un usuario
     * @param type $idUsusario
     * @return type
     */
    public function getCalificacionesOf($idUsusario){
        $sql = "select * 
            from calificaciones 
            where id_calificante = :id_calificante
            order by fecha_crea";
        $params = array(
            ":id_calificante" => $idUsuario
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }
    /**
     * Retorna todas las calificaciones recibidas de un usuario
     * @param type $yoMismo
     * @return array
     */
    public function getAllCalificacionesRecibidas($yoMismo){
        $sql = "select * 
            from calificaciones 
            where id_calificado = :yoMismo
            order by fecha_crea";
        $params = array(
            ":yoMismo" => $yoMismo
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }
    /**
     * Retorna una calificacion
     * @param type $idUsusario
     * @return type
     */
    public function getCalificacion($idCali){
        $sql = "select * 
            from calificaciones 
            where id = :id_cali";
        $params = array(
            ":id_cali" => $idCali
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
    }

    /**
     * crea una calificacion
     * @param type $idUsusario
     * @return type
     */
    public function crearCalificacion($idviaje, $calificante = null, $calificado = null){
        $sql = "insert INTO calificaciones (id_viaje, id_calificante, id_calificado, fecha_crea, fecha_modi)
            VALUES (:id_viaje, :id_calificante, :id_calificado, NOW(), NOW())";
        $params = array(
            ":id_viaje" => $idviaje,
            ":id_calificante" => $calificante,
            ":id_calificado"  => $calificado
     
        );
        $this->_db->execute($sql, $params);
    }
}
?>


