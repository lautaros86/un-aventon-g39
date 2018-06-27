<?php

class facturaModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crea una nueva factura
     * @param int $tipo Determina el tipo de factura, de chofer(1 int) o pasajero(2 int).
     */
    public function crearFactura($idUsuario, $idViaje, $monto, $descripcion, $tipo, $estado = 1) {
        $sql = "INSERT INTO facturas (id_usuario, id_viaje, monto, descripcion, id_tipo, id_estado, fecha_crea, fecha_modi) 
                VALUES (:id_usuario, :id_viaje, :monto, :descripcion, :tipo, :estado, NOW(), NOW())";
        $params = array(
            ":id_usuario" => $idUsuario,
            ":id_viaje" => $idViaje,
            ":monto" => $monto,
            ":descripcion" => $descripcion,
            ":tipo" => $tipo,
            ":estado" => $estado,
        );
        $this->_db->execute($sql, $params);
    }
    
    /**
     * Retorna todas las facturas asociadas a un usuario
     * @param type $form
     */
    public function getFacturasOf($idUsuario) {
        $sql = "select f.id as id_factura, ef.nombre as estadofactura, ev.nombre as estadoviaje, 
            tf.nombre as tipofactura, v.monto as monto_viaje, f.monto as factura_monto, f.*, v.* 
            from facturas f inner join viaje v on (f.id_viaje = v.id)
            inner join estado_factura ef on (f.id_estado = ef.id)
            inner join tipo_factura tf on (f.id_tipo = tf.id)
            inner join estado_viaje ev on (v.id_estado = ev.id)
            where id_usuario = :id_usuario";
        $params = array(
            ":id_usuario" => $idUsuario
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }    
        
    
    /**
     * Retorna todas las facturas asociadas a un usuario en estado activo ( que debe pagarse)
     * @param type $form
     */
    public function getFacturasActivasOf($idUsuario) {
        $sql = "select * from facturas where id_usuario = :id_usuario and id_estado = 2";
        $params = array(
            ":id_usuario" => $idUsuario
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
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
        return $this->_db->fetchAll();
    }
    
    
    /**
     * Retorna todas las facturas 
     * @param type $form
     */
    public function getFactura($idFactura) {
        $sql = "select * from facturas 
            inner join viaje on facturas.id_viaje = viaje.id
            where facturas.id = :id_factura";
        $params = array(
            ":id_factura" => $idFactura
        );
        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
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
     * cambia el estado de una factua a PAGA(3) 
     * @param type $form
     */
    public function pagarFactura($idFactura, $medio = "indefinido") {
        $sql = "update facturas set id_estado = 3, medio = :medio, fecha_pago = NOW()  where id = :id_factura";
        $params = array(
            ":id_factura" => $idFactura,
            ":medio" => $medio
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


