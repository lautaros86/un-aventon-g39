<?php

class tarjetaModel extends Model {

    public function __construct() {
        parent::__construct();
    }
    /**
     * recibo el id del usuario y retorno todas sus tarjetas de crediro 
     * @param type $id
     * @return array
     */
    public function getTarjetasDeUnUsuario($id) {
        $sql = "select * from tarjeta where id_usuario = :id ";
        $params = array(":id"=>$id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetchAll();
    }
    /**
     * retorna con el id de un usuario todas sus tarjetas de credito
     * @param type $id
     * @return array
     */
    public function getTarjetaDeUnUsuario($id) {
        $sql = "select * from tarjeta where id_usuario = :id";
        $params = array(":id"=> $id);
        $this->_db->execute($sql, $params);
        return $this->_db->fetch();
    }
    //terminar de modificar
    public function insertarTarjeta($form, $idUsuario) {
        // VALUES 
        $sql = "INSERT INTO tarjeta VALUES (NULL,:numero, :nombre, :vtoMes, :vtoAnio, :idUser)";
               
        $params = array(":numero"=> $form["numero"], 
                        ":nombre"=>$form["nombre"], 
                        ":vtoMes"=>$form["mesVencimiento"],
                        ":idUser"=>$idUsuario, 
                        ":vtoAnio"=>$form["anioVencimiento"]);
        $this->_db->execute($sql, $params);
    }
    //terminar de modificar
    public function darDeBajaTarjeta ($id){
        $sql="UPDATE vehiculo SET estado = 1 WHERE id=:idTarjeta";
        $params= array (":idTarjeta"=>$id);
        $this->_db->execute($sql, $params);
        //return $this->db->fetch();
    }
    /**
     * consulta por numero de tarjeta recibe el numero de tarjeta y el id de un usuario y verifica si el usuario ya tiene esa tarjeta
     */  
    public function consultarPorRepetido ($numeroTarjeta,$idUsuario){
       
       $sql= "SELECT COUNT(*) as cantidad FROM tarjeta WHERE (numero=:numero) and (id_usuario=:idUsuario)"; 
       
       $params = array(":numero"=>$numeroTarjeta, ":idUsuario" =>$idUsuario);
       
       $this->_db->execute($sql, $params);
       return $this->_db->fetch();
    }
    /**
     * da de baja una tarjeta con su numero de tarjeta como parametro
     */
    //terminar de modificar
    public function eliminarTarjeta($id) {
        (int) $id;
        $sql = "UPDATE usuarios SET estado = 2 WHERE id = :id";
        $this->_db->execute($sql, array(':id' => $id));
    }
}
