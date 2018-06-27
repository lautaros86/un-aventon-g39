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


