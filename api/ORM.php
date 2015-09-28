<?php
/** This class is used for creating and loading and saving and deleting instances of class Model to the DB */
class ORM {

    public static function create ($class_name, $properties) {
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

    public static function retrieve ($config) {
        $class_name = $config['name'];
        $wheres = $config['properties'];
        $with_relations = $config['with_relations'];
        $force = $config['force'];

        $models = [];
        $model_name = ucfirst($class_name . 'Model');
        if (class_exists($model_name)) {
            $table_name = $model_name::get_table_name();
            $prop_filter = $model_name::get_properties(Access::READ, $_SESSION['permissions'], $force);
            $column_selector = '0';
            if (is_array($prop_filter) && sizeof($prop_filter) > 0) {
                $column_selector = implode($prop_filter, ',');
            }
            $query = 'SELECT ' . $column_selector . ' FROM `' . $table_name . '` WHERE ';
            $where_selector = 'TRUE';
            $wheres_count = sizeof($wheres);
            if (is_array($wheres)) {
                if ($wheres_count > 0) {
                    $keys = array_keys($wheres);
                    foreach ($keys as $key) {
                        $query .= $key . '=:' . $key . ' AND ';
                    }
                }
            }
            // remove last " AND "
            $query = substr($query, 0, strlen($query) - 5);

            // connect to the database
            $connection = NULL;
            try {
                $connection = new PDO('mysql:host=' . DB::HOSTNAME . ';dbname=' . DB::DBNAME, DB::USERNAME, DB::PASSWORD);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            }
            catch(PDOException $e) {
                echo "I'm sorry, Dave. I'm afraid I can't do that.";
                file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
            }
            if ($connection != NULL) {
                $statement = $connection->prepare($query);
                $statement->execute($wheres);
                $models = $statement->fetchAll(PDO::FETCH_CLASS, $model_name);
                if ($with_relations) {
                    foreach ($models as $model) {
                        $model->relations = new stdClass();
                        $model->relations->belongs_to = static::retrieve_belongs_to($model);
                        $model->relations->belongs_to_and_has_many = static::retrieve_belongs_to_and_has_many($model);
                        $model->relations->has_many = [];
                    }
                }
            }
        } else {
            throw new Exception("Did not find class with class name '" . $class_name . "'.");
        }
        return $models;
    }

    public static function retrieve_has_many ($model) {
        $has_many = [];
        $class = get_class($model);
        if ($model->id === NULL) {
            if ($class::get_has_many() != NULL) {
                // get list of classes which this objects belongs to
                $has_many_description = $class::get_has_many();
                $configs = [];
                if (is_string($has_many_description)) {
                    $props = [$class . '_id' => $model->id];

                    $conf = [
                        'name' => $has_many_description . 's',
                        'properties' => $props,
                    ];
                    array_push($configs, $conf);
                } else if (is_object($has_many_description)) {

                } else if (is_array($has_many_description)) {

                }
                foreach ($configs as $config) {
                    $config['width_relations'] = false;
                    $config['force'] = true;
                    $has_many = $this->retrieve($config);
                }
                // $id = $model->id;
                // array_push($has_many, $id);
            }
        } else {
            throw new Exception('Could not get has_many relations from DB when the model has no id set');
        }
        return $has_many;
    }

    private static function retrieve_belongs_to ($model) {
        // table structure:
        // | id | foreign_id | name |
        $has_many = [];
        // $class = 
        return $has_many;
    }

    private static function retrieve_belongs_to_and_has_many ($model) {
        $relations = [];
        $model = get_class();
        if ($class::get_belongs_to_and_has_many() != NULL) {
            
        }
        return $relations;
    }

    public static function save ($models) {
        $query = "INSERT INTO ";
        $class_name = NULL;
        $inserts = [];
        $updates = [];
        if (is_array($models) && sizeof($models) > 0) {
            $class_name = get_class($models[0]);
            foreach ($models as $model) {
                $tmp_class_name = get_class($model);
                if ($tmp_class_name != $class_name) {
                    throw new Exception('ORM does not support saving different classes. ' . $class_name . ' expected, but got ' . $tmp_class_name . ' instead.');
                }
                if ($model->id === NULL) {
                    array_push($inserts, $model);
                } else {
                    array_push($updates, $model);
                }
            }
        } else if (is_object($models)) {
            $class_name = get_class($models);
            if ($models->id === NULL) {
                array_push($inserts, $models);
            } else {
                array_push($updates, $models);
            }
        }

        if (sizeof($inserts) > 0) {
            $query = 'INSERT INTO ' . $class_name::get_table_name() . '(';
            //$class_name::get_allowed_columns();
        }
        if (sizeof($updates) > 0) {

        }


        // $stmt = $db->prepare("INSERT INTO table(field1,field2,field3,field4,field5) VALUES(:field1,:field2,:field3,:field4,:field5)");
        // $stmt->execute(array(':field1' => $field1, ':field2' => $field2, ':field3' => $field3, ':field4' => $field4, ':field5' => $field5));
        // $affected_rows = $stmt->rowCount();
        // $connection = NULL;
        // try {
        //     $connection = new PDO('mysql:host=' . DB::HOSTNAME . ';dbname=' . DB::DBNAME, DB::USERNAME, DB::PASSWORD);
        //     $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        // }
        // catch(PDOException $e) {
        //     echo "I'm sorry, Dave. I'm afraid I can't do that.";
        //     file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        // }
        // if ($connection != NULL) {
        //     $statement = $connection->prepare($query);
        //     $statement->execute($wheres);
        //     $models = $statement->fetchAll(PDO::FETCH_CLASS, $model_name);
        // }
    }

    public static function delete ($models) {
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