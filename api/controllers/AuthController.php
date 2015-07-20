<?php

require 'DB.php';

abstract class AuthController {

    private $db_conf;
    private $connection;

    public function __construct($configuration_model, $configuration) {
        $this->db_conf = $configuration->db;
    }
    
    public function __call($name, $arguments) {
        $this->init();
    }

    protected function init () {
        $this->connection = new PDO('mysql:host='.$this->db_conf->host.';charset=utf8', $this->db_conf->user, $this->db_conf->password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    

    // protected function create ($values) {
    //     if ($this->connection !== null) {
    //         $this->connection = new PDO('mysql:host='.$this->db_conf->host.';charset=utf8', $this->db_conf->user, $this->db_conf->password);
    //         $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //         $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //     }
    //     return $this->connection->query($query);
    // }

    // protected function read ($id, $values) {
    //     return '';
    // }

    // protected function update ($id, $values) {

    // }

    // protected function delete ($id) {

    // }
}

?>