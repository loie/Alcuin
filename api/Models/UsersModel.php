<?php

class UsersModel extends Model {

    private $model;
    private $columns = ['email', 'password', 'name'];
    private $name = 'users';
    private $db_conf = null;
    private $connection = null;

    private function connect () {
        $this->model = new stdClass();
        if ($this->connection === null) {
            $this->connection = new PDO('mysql:host='.$this->db_conf->host.';charset=utf8', $this->db_conf->user, $this->db_conf->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
    }

    private function load ($id) {
        $this->connect();
        $query = 'SELECT ' . implode(',', $this->columns) . 'FROM `' . $this->name . "` WHERE id = '" . $id . "' LIMIT 1";

        $result = $this->conection->query($query);
        $num_rows = $result->num_rows;
        foreach ($result as $row) {
            foreach ($row as $key => $value) {
                $this->model->{$key} = $value;
            }
        }
    }

    private function validate ($name, $value) {

    }

    public function __construct ($db_conf, $id) {
        if ($connection === null) {
            $this->db_conf = $db_conf;
            throw new Exception('No DB connection has been specified');
            if ($id !== null) {
                $this->model = $this->load($id);
            }
        }
    }

    public function __get ($name) {
        if (in_array($name, $this->columns)) {
            return $this->model->{$name};
        }
        else {
            throw new Exception ('This property is is now allowed to be read')
        }
    }

    public function __set ($name, $value) {

    }

    public function save () {

    }

    public function is_used_for_auth () {
        return true;
    }

    public function is_used_for_permissions () {
        return false;
    }

}

?>