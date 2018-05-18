<?php

class DatabasePDO extends PDO {

    private $PDO;
    private $query;

    public function __construct() {
        try {
            $this->PDO = new PDO('mysql:host=' . DB_HOST .
                    ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES  \'UTF8\''));
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "¡Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    public function getPdo() {
        return $this->PDO;
    }

    public function closePdo() {
        return $this->PDO = null;
    }

    public function prepare($sql, $options = array()) {
        $this->query = $this->PDO->prepare($sql, $options);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->query->bindValue($param, $value, $type);
    }

    public function execute($sql, $params = null) {
        $this->prepare($sql);
        if (!$this->query->execute($params)) {
            return false;
        }
        return true;
    }

    public function fetchall($conf = PDO::FETCH_ASSOC) {
        //OJO
        //Si acepta el parametro cuando devuelve los resultados los va a devolver como un array que los titulos seran los campos
        //Si no le paso ningun parametro devolvera los resultados en un array con los titulos y a su vez con indices
        //o sea que podria llamar a un dato por su indice [0] en vez de [id]
        //El fetchall con parametro se dejo para usarlo desde los datatables donde UNICAMENTE puedo usar esa opcion, sino duplicaria los registros.
        return $this->query->fetchAll($conf);
    }

    public function fetch($conf = null) {
        return $this->query->fetch($conf);
    }

    public function rowCount() {
        return $this->query->rowCount();
    }

    public function lastInsertId($seqname = NULL) {
        return $this->PDO->lastInsertId($seqname);
    }

    public function beginTransaction() {
        return $this->PDO->beginTransaction();
    }

    public function commitTransaction() {
        return $this->PDO->commit();
    }

    public function rollBackTransaction() {
        return $this->PDO->rollBack();
    }

}
?>

