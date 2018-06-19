<?php

class DatabasePDO extends PDO {

    protected static $instance = null;
    private $PDO;
    private $query;
    private $transactionCounter;

    protected function __construct() {
        try {
            $this->PDO = new PDO('mysql:host=' . DB_HOST .
                    ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES  \'UTF8\''));
            $this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "¡Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    private function __clone() {
        
    }

    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
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

    public function bindValue($param, $value, $type = null) {
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

    public function bindParam($param, $value, $type = null) {
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
        $this->query->bindParam($param, $value, $type);
    }

    public function bindExecute() {
        if (!$this->query->execute()) {
            return false;
        }
        return true;
    }

    public function execute($sql, $params = null) {
        $this->prepare($sql);
        if (!$this->query->execute($params)) {
            return false;
        }
        return true;
    }

    public function fetchAll($conf = PDO::FETCH_ASSOC) {
        //OJO
        //Si acepta el parametro cuando devuelve los resultados los va a devolver como un array que los titulos seran los campos
        //Si no le paso ningun parametro devolvera los resultados en un array con los titulos y a su vez con indices
        //o sea que podria llamar a un dato por su indice [0] en vez de [id]
        //El fetchall con parametro se dejo para usarlo desde los datatables donde UNICAMENTE puedo usar esa opcion, sino duplicaria los registros.
        return $this->query->fetchAll($conf);
    }

    public function fetch($conf = PDO::FETCH_ASSOC) {
        return $this->query->fetch($conf);
    }

    public function rowCount() {
        return $this->query->rowCount();
    }

    public function lastInsertId($seqname = NULL) {
        return $this->PDO->lastInsertId($seqname);
    }

    public function beginTransaction() {
        if (!$this->transactionCounter++) {
            return $this->PDO->beginTransaction();
        }
        $this->PDO->exec('SAVEPOINT trans' . $this->transactionCounter);
        return $this->transactionCounter >= 0;
    }

    public function commit() {
        if (!--$this->transactionCounter) {
            return $this->PDO->commit();
        }
        return $this->transactionCounter >= 0;
    }

    public function rollback() {
        if (--$this->transactionCounter) {
            $this->PDO->exec('ROLLBACK TO trans' . $this->transactionCounter + 1);
            return true;
        }
        return $this->PDO->rollback();
    }

}
?>

