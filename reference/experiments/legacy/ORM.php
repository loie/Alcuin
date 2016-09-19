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

        $models = [];
        $model_name = ucfirst($class_name . 'Model');
        $table_name = NULL;
        $prop_filter = NULL;

        if ($config['use_plain_object']) {
            $table_name = $class_name;
            $prop_filter = '*';
        } else {
            $class_exists = class_exists($model_name);
            if ($class_exists) {
                $table_name = $model_name::get_table_name();
                $prop_filter = $model_name::get_properties(Access::READ, $_SESSION['permissions'], $config['force']);
            }
        }
        $column_selector = '0';
        if (is_array($prop_filter) && sizeof($prop_filter) > 0) {
            $column_selector = implode($prop_filter, ',');
        } else if ($prop_filter === '*') {
            $column_selector = $prop_filter;
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
        $connection = static::connect();
        
        if ($connection === NULL) {
            throw new Exception("Did not find class with class name '" . $class_name . "'.");
        } else {
            $statement = $connection->prepare($query);
            try {
                $statement->execute($wheres);
            } catch (PDOException $e) {
                echo 'Could not execute, <pre>' .$e->getMessage() . '</pre>';
            }
            if ($config['use_plain_object']) {
                $models = $statement->fetchAll(PDO::FETCH_OBJ);
            } else {
                $models = $statement->fetchAll(PDO::FETCH_CLASS, $model_name);
            }
            if ($config['with_relations']) {
                if ($config['use_plain_object']) {
                    throw new Exception('Cannot "use_plain_object" getting type combined "with_relations"');
                }
                else {
                    foreach ($models as $model) {
                        $model->relations = new stdClass();
                        $model->relations->has_many = static::retrieve_has_many($model);
                        $model->relations->belongs_to = static::retrieve_belongs_to($model);
                        $model->relations->belongs_to_and_has_many = static::retrieve_belongs_to_and_has_many($model);
                    }           
                }
            }
        }
        return $models;
    }

    public static function retrieve_has_many ($model) {
        $has_many = [];
        $class = get_class($model);
        if ($model->id === NULL) {
            throw new Exception('Could not get has_many relations from DB when the model has no id set');
        } else {
            if ($class::get_has_many() != NULL) {
                // get list of classes which this objects belongs to
                $has_many_description = $class::get_has_many();
                $configs = [];
                if (is_array($has_many_description)) {
                    foreach ($has_many_description as $description) {
                        $conf = $static::get_has_many_configuration($model, $description);
                        array_push($configs, $conf);
                    }
                } else {
                    array_push($configs, static::get_has_many_configuration($model, $has_many_description));
                }
                foreach ($configs as $config) {
                    $config['with_relations'] = false;
                    array_push($has_many, $this->retrieve($config));
                }
            }
        }
        return $has_many;
    }

    private static function get_has_many_configuration ($model, $description) {
        $name = '';
        $class = get_class($model);
        $value = $model->id;
        if (is_string($description)) {
            $column_name = $class . '_id';
            $table_name = $description . 's';
        } else if (is_object($description)) {
            $column_name = $description->name . '_id';
            $table_name = $description->name . 's';
        }
        $props = [$column_name => $value];
        $conf = [
            'name' => $table_name,
            'properties' => $props
        ];
        return $conf;
    }

    private static function retrieve_belongs_to ($model) {
        // table structure:
        // | id | foreign_id | name |
        $belongs_to = [];
        $class = get_class($model);
        if ($model->id === NULL) {
            throw new Exception('Could not get belongs_to relations from DB when the model has no id set');
        } else {
            if ($class::get_belongs_to() != NULL) {
                // get list of classes which this objects belongs to
                $belongs_to_description = $class::get_belongs_to();
                $configs = [];
                if (is_array($belongs_to_description)) {
                    foreach ($belongs_to_description as $description) {
                        $conf = $static::get_belongs_to_configuration($model, $description);
                        array_push($configs, $conf);
                    }
                } else {
                    array_push($configs, static::get_belongs_to_configuration($model, $belongs_to_description));
                }
                foreach ($configs as $config) {
                    $config['with_relations'] = false;
                    array_push($belongs_to, $this->retrieve($config));
                }
            }
        }
        return $belongs_to;
    }

    private static function get_belongs_to_configuration ($model, $description) {
        $name = '';
        $class = get_class($model);
        $value = $model->id;
        $column_name = 'id';
        if (is_string($description)) {
            $table_name = $description . 's';
        } else if (is_object($description)) {
            $table_name = $description->name . 's';
        }
        $props = [$column_name => $value];
        $conf = [
            'name' => $table_name,
            'properties' => $props
        ];
        return $conf;
    }

    private static function retrieve_belongs_to_and_has_many ($model) {
        $relations = [];
        if ($model->get_belongs_to_and_has_many() !== NULL) {
            // Get Ids from intermediate table
            foreach ($model->get_belongs_to_and_has_many() as $belongs_to_and_has_many_description) {
                $conf = static::get_belongs_to_and_has_many_configuration($model, $belongs_to_and_has_many_description);
                $id_records = static::retrieve($conf);
                $records = [];
                foreach ($id_records as $id_record) {
                    $table_name = $belongs_to_and_has_many_description->model . 's';
                    $property_name = $belongs_to_and_has_many_description->name . '_id';
                    $props = ['id' => $id_record->{$property_name}];
                    $conf = [
                        'name' => $table_name,
                        'properties' => $props,
                        'with_relations' => false
                    ];
                    $objects = static::retrieve($conf);
                    foreach ($objects as $object) {
                        array_push($records, $object);
                    }
                }
                $relations[$belongs_to_and_has_many_description->name . 's'] = $records;
            }
        }
        return $relations;
    }

    private static function get_belongs_to_and_has_many_configuration ($model, $description) {
        $column_name = '';
        $value = $model->id;
        if (is_object($description)) {
            $column_name = $model->get_id_column_name() . '_id';
            $table_name = $description->via_table;
        }
        $props = [$column_name => $value];
        $conf = [
            'name' => $table_name,
            'properties' => $props,
            'force' => true,
            'use_plain_object' => true
        ];
        return $conf;
    }

    public static function save ($models) {
        if (is_object($models)) {
            static::save_model($models);
        } else if (is_array($models)) {
            foreach ($models as $model) {
                static::save_model($model);
            }
        }
    }

    private static function save_model ($model) {
        if (is_numeric($model->id)) {
            static::update_model($model);
        } else if ($model->id === NULL) {
            static::insert_model($model);
        }
    }

    private static function update_model ($model) {
        $query = 'UPDATE ' . $model->get_table_name() . ' SET ';
        $properties = $model->get_properties(Access::UPDATE, $_SESSION['permissions']);
        foreach ($properties as $property) {
            $query .= '`' . $property . '`=:' . $property . ',';
        }
        // remove last ','
        $query = substr($query, 0, strlen($query) - 1);
        $query .= " WHERE `id`=:id";
        $connection = static::connect();
        $statement = $connection->prepare($query);
        $fields = get_object_vars($model);
        $filtered = [];
        // filter by is_string
        foreach ($fields as $key => $value) {
            if (is_object($value)) {
                // do nothing
            } else {
                $filtered[$key] = $value;
            }
        }
        try {
            $statement->execute($filtered);
        } catch (PDOException $e) {
            echo 'Could not execute, <pre>' .$e->getMessage() . '</pre>';
        }

        // UPDATE `test`.`users` SET `token`='as', `token_last_updated`='0000-00-00 00:00:01' WHERE `id`='1';
    }

    private static function insert_model ($model) {

    }

    public static function delete ($models) {
        if (is_array($models)) {

        }
    }




    /*
    *   Connect
    */
    private static function connect () {
        $connection = null;
        try {
            $connection = new PDO('mysql:host=' . DB::HOSTNAME . ';dbname=' . DB::DBNAME, DB::USERNAME, DB::PASSWORD);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch(PDOException $e) {
            echo "I'm sorry, Dave. I'm afraid I can't do that.";
            file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
        }
        return $connection;
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