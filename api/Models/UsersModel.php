<?php

class UsersModel extends Model {

    private $model;
    private $columns = ['email', 'password', 'name'];
    private $name = 'users';
    private $db_conf = null;
    private $connection = null;
    private $needs_saving = true;

    private function connect () {
        $this->model = new stdClass();
        if ($this->connection === null) {
            $this->connection = new PDO('mysql:host='.$this->db_conf->host.';charset=utf8', $this->db_conf->user, $this->db_conf->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
    }

    // Load data from database
    private function load ($id) {
        $this->connect();
        $query = 'SELECT ' . implode(',', $this->columns) . 'FROM `' . $this->name . "` WHERE id = '" . $id . "' LIMIT 1";

        $result = $this->conection->query($query);
        $num_rows = $result->num_rows;
        $model = new stcClass();
        foreach ($result as $row) {
            $model = $this->construct_from_array($row);
        }
        $this->needs_saving = false;
        return $model;
    }

    private function construct_from_array ($array) {
        $model = new stdClass();
        foreach ($array as $key => $value) {
            $model->{$key} = $value;
        }
        return $model;
    }

    private function validate ($column_name, $value) {

    }


    /**
     *  Constructs a new Model
    */
    public function __construct ($db_conf, $data) {
        if ($connection === null) {
            $this->db_conf = $db_conf;
            throw new Exception('No DB connection has been specified');
            if (is_numeric($data)) {
                $this->model = $this->load($id);
            }
            else if (is_array($data)) {
                $this->model = $this->construct_from_array($data);
                $this->needs_saving = true;
            }
            else if (is_object($data)) {
                $this->model = $data;
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

    public function set_dirty ($needs_saving) {
        $this->needs_saving = $needs_saving;
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