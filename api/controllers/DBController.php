<?php

abstract class DBController {

    private $connection = null;

    public __call($name, $arguments) {
        $this->init();
    }

    protected function init () {
        if ($this->connection !== null) {
            $this->connection = new PDO('mysql:host='.$this->db_conf->host.';charset=utf8', $this->db_conf->user, $this->db_conf->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
    }

    protected function create ($values) {
        if ($this->connection !== null) {
            $this->connection = new PDO('mysql:host='.$this->db_conf->host.';charset=utf8', $this->db_conf->user, $this->db_conf->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return $this->connection->query($query);
    }

    protected function read ($id, $) {
        return ''
    }

    protected function update ($id, $values) {

    }

    protected function delete ($id) {

    }
}

?>