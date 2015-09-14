<?php
/** This class is used for creating and loading and saving and deleting instances of class Model to the DB */
class ORM {

    public static function create($class_name, $properties) {
        $model = null;
        $model_name = ucfirst($class_name . 'Model');
        if (class_exists($model_name)) {
            $model = new $model_name();
            foreach (get_object_vars($properties) as $key => $value) {
                $model->{$key} = $value;
            }
        } else {
            throw new Exception("Did not find class with class name '" . $class_name . "'.");
        }
        return $model;
    }

    public static function retrieve($class_name, $wheres) {
        $models = [];
        $model_name = ucfirst($class_name . 'Model');
        if (class_exists($model_name)) {
            $table_name = $model_name::get_table_name();
            $column_filter = $model_name::get_column_filter();
            $column_selector = '*';
            if (is_array($column_filter) && sizeof($column_filter) > 0) {
                $column_selector = implode($column_filter, ',');
            }
            $query = 'SELECT ' . $column_selector . ' FROM `' . $table_name . '` WHERE (';
            $where_selector = 'TRUE';
            $wheres_count = sizeof($wheres);
            if (is_array($wheres)) {
                if ($wheres_count > 0) {
                    $keys = array_keys($wheres);
                    foreach ($keys as $key) {
                        $query .= '`' . $key . '` = :' . $key . ',';
                    }
                }
            }
            // remove last comma
            $query = substr($query, 0, strlen($query) - 1);
            $query .= ')';

            // connect to the database
            try {
                $connection = new PDO('mysql:host=' . DB::HOSTNAME . ';dbname=' . DB::DBNAME, DB::USERNAME, DB::PASSWORD);
                $connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $connection->prepare($query);
                $response = $connection->query($wheres);
                $response->setFetchMode(PDO::FETCH_CLASS, $model_name);
                while ($row = $response->fetch()) {
                    echo '<pre>';
                    print_r($row);
                    echo '</pre>';
                }
            }
            catch(PDOException $e) {
                echo "I'm sorry, Dave. I'm afraid I can't do that.";
                file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
            }
        } else {
            throw new Exception("Did not find class with class name '" . $class_name . "'.");
        }
        return $models;
    }

    public static function save($models) {
        if (is_array($models)) {

        }
    }

    public static function delete($models) {
        if (is_array($models)) {

        }
    }




    //////////
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

    ////////////
}
?>