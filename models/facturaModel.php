<?php

class facturaModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crea una nueva factura
     * @param int $tipo Determina el tipo de factura, de chofer(1 int) o pasajero(2 int).
     */
    public function crearFactura($idUsuario, $idViaje, $monto, $descripcion, $tipo) {
        $sql = "INSERT INTO facturas (id_usuario, id_viaje, monto, descripcion, fecha_crea, fecha_modi) 
                VALUES (:id_usuario, :id_viaje, :monto, :descripcion, NOW(), NOW())";
        $params = array(
            ":id_usuario" => $idUsuario,
            ":id_viaje" => $idViaje,
            ":monto" => $monto,
            ":descripcion" => $descripcion
        );
        $this->_db->execute($sql, $params);
    }
    
    /**
     * Retorna todas las facturas asociadas a un usuario
     * @param type $form
     */
    public function getFacturasOf($idUsuario) {
        $sql = "select * from facturas where id_usuario = :id_usuario";
        $params = array(
            ":id_usuario" => $idUsuario
        );
        $this->_db->execute($sql, $params);
    }    
        
    /**
     * Retorna todas las facturas asociadas a un usuario no pagadas o pendientes.
     * @param type $form
     */
    public function getFacturasPendinetesOf($idUsuario) {
        $sql = "select * from facturas where id_usuario = :id_usuario and fecha_pago = null";
        $params = array(
            ":id_usuario" => $idUsuario
        );
        $this->_db->execute($sql, $params);
    }    
    
    /**
     * Retorna todas las facturas 
     * @param type $form
     */
    public function getFacturas() {
        $sql = "select * from facturas";
        $params = array(
            ":id_usuario" => $idUsuario
        );
        $this->_db->execute($sql, $params);
    }
    
    
    /**
     * cambia el estado de una factua a ACTIVA(2) 
     * @param type $form
     */
    public function activarFactura($idFactura) {
        $sql = "update facturas set id_estado = 2 where id = :id_factura";
        $params = array(
            ":id_factura" => $idFactura
        );
        $this->_db->execute($sql, $params);
    }
    
    /**
     * cambia el estado de una factua a ACTIVA(2) 
     * @param type $form
     */
    public function activarFacturaDeViaje($idViaje) {
        $sql = "update facturas set id_estado = 2 where id_viaje = :id_viaje";
        $params = array(
            ":id_viaje" => $idViaje
        );
        $this->_db->execute($sql, $params);
    }
    
    
    

}
?>


