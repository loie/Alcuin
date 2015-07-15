<?php

class Main {

    private $db = NULL;
    private $db_conf = NULL;
    private $config_file = NULL;
    private $configuration = NULL;

    public function __construct($config_file) {
        $this->config_file = $config_file;
        echo '<li>Searching for configuration file <code>' . $this->config_file . '</code>&hellip; ';
        if (file_exists($config_file)) {
            $this->success('Trying to parse configuration file <code>' . $config_file . '</code>');
            try {
                $this->configuration = new Configuration($config_file);
                assert($this->configuration != NULL);
                $this->success('Checking for database settings');
                $this->db_conf = $this->configuration->db;
                if ($this->db_conf == NULL) {
                    $this->error('The file <code>'.$config_file.'</code> has not defined a database configuration');
                } else {
                    $this->success();
                }
            }
            catch (Exception $e) {
                $this->error('The file <code>'.$this->config_file.'</code> is not in valid JSON format');
            }
        } else {
            $this->error('The file <code>'.$config_file.'</code> does not exists. Have you forgotten to ');
        }
    }

    private function get_missing_prop($obj, $prop_array) {
        foreach ($prop_array as $prop) {
            if ($obj->{$prop} == null) {
                return $prop;
            }
        }
        return null;
    }

    private function next($next) {
        echo '<li>' . $next . '&hellip; ';
    }

    private function success($next_q = NULL) {
        echo '<strong class="text-success">Success</strong></li>';
        if ($next_q !== NULL) {
            $this->next($next_q);
        }
    }

    private function error($error, $details) {
        echo '<strong class="text-danger">Failed</strong></li>';
        echo '</ul>';
        echo '<div class="alert alert-danger" role="alert"><strong>An error occured:</strong> ' . $error;
        if ($details != null) {
            echo '<hr /><strong>Error message:</strong>';
            echo '<pre>' . $details . '</pre>';
        }
        echo '</div>';
        die();
    }

    private function init() {
        $this->db = new PDO('mysql:host='.$this->db_conf->host.';charset=utf8', $this->db_conf->user, $this->db_conf->password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    private function ping($query) {
        try {
            $this->db->query('SELECT 1');
        } catch (PDOException $e) {
            $this->init();
        }
        $this->db->exec($query);
    }

    private function create_model_in_db($model) {
        // echo '<pre>';
        // print_r($model);
        // echo '</pre>';

        $model_name = $model->name . 's';
        $query_string = 'CREATE TABLE `' . $this->db_conf->name . '`.`' . $model_name . '` (';
        $statements = [];
        $relation_statements = [];
        $index_statements = [];
        $constraint_statements = [];

        array_push($statements, "`id` INT NOT NULL AUTO_INCREMENT COMMENT 'Primary Key for this table' ");

        // Insert relationship columns first
        if (isset($model->belongs_to) && is_array($model->belongs_to)) {
            foreach ($model->belongs_to as $relation) {
                $relation_name = NULL;
                $column_name = NULL;
                $index_name = NULL;
                $table_name = NULL;
                if (is_object($relation)) {
                    $relation_name = $relation->name;
                    $column_name = $relation->name . '_id';
                    $table_name = $relation->model;
                }
                else if (is_string($relation)) {
                    $relation_name = $relation;
                    $column_name = $relation . '_id';
                    $table_name = $relation;
                }
                $index_name = $column_name . '_INDEX';
                array_push($statements, '`' . $column_name . '` INT NOT NULL');
                array_push($index_statements, 'INDEX `' . $index_name . '` (`' . $column_name . '` ASC)');
                array_push($constraint_statements, 'FOREIGN KEY (`' . $column_name . '`) REFERENCES `' . $this->db_conf->name . '`.`' . $table_name . 's` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
            }
        }
        if (isset($model->properties)) {
            foreach ($model->properties as $property) {
                $prop = new Property($property);
                array_push($statements, $prop->get_db_column_statement());
                $index_statement = $prop->get_db_column_index_statements();
                if ($index_statement !== NULL) {
                    array_push($index_statements, $index_statement);
                }
            }
        }
        array_push($statements, 'PRIMARY KEY (`id`)');
        $all_statements = array_merge($statements, $index_statements, $constraint_statements);
        $query_string .= implode(', ', $all_statements);
        $query_string .= ');';

        // echo '<pre>' . $query_string . '</pre>';

        $this->ping($query_string);

        if (isset($model->belongs_to_and_has_many) && is_array($model->belongs_to_and_has_many)) {
            foreach($model->belongs_to_and_has_many as $relation) {
                $relation_model = new stdClass();
                if (is_string($relation)) {
                    $relation_model->name = $model->name . 's_' . $relation;
                }
                else if (is_array($relation)) {
                    $relation_model->name = $model->name . 's_' . $relation->name;
                }
                $relation_model->belongs_to = [$model->name, $relation];
                $this->create_model_in_db($relation_model);
            }
        }

        if (isset($model->instances) && is_array($model->instances)) {
            foreach ($model->instances as $instance) {
                $insert_statement = "INSERT INTO " . $model_name . " ({keys}) VALUES ({values})";
                $names = array();
                $values = array();
                foreach (get_object_vars($instance) as $key => $value) {
                    echo print_r(get_object_vars($instance));
                    array_push($names, $key);
                    array_push($values, "'" . $value . "'");
                    $insert_statement = str_replace('{keys}', implode(',', $names), $insert_statement);
                    $insert_statement = str_replace('{values}', implode(',', $values), $insert_statement);
                    $this->db->exec($insert_statement);
                }
            }
        }
    }

    private function create_model_in_filesystem($model) {
        $this->success('Creating Model class for ' . $model);
    }

    private function create_controller_in_filesystem($model) {
        $this->success('Creating controller class for ' . $model);
    }


    public function exec() {
        // check for existing of configuration.json
        $this->next('Checking database properties');
        $db_required_props = array('host', 'name', 'user', 'password');
        $missing_prop = $this->get_missing_prop($this->db_conf, $db_required_props);
        if ($missing_prop == null) {
            $this->success('Connecting to server');
            try {
                $this->init();
                try {
                    if ($this->configuration->drop_old_db) {
                        $this->success('Dropping old database because of configuration');
                        try {
                            $this->db->exec('DROP SCHEMA IF EXISTS `' . $this->db_conf->name . '`');
                        }
                        catch (Exception $e) {
                            $this->error('Could not drop old database ' . $this->db_conf->name, $e->getMessage());
                        }
                    }
                    $this->success('Checking whether database "' . $this->db_conf->name . '" exists and creating a new one if it doesn\'t exists');
                    try {
                        $this->db->exec('CREATE SCHEMA IF NOT EXISTS`' . $this->db_conf->name . '` DEFAULT CHARACTER SET utf8 ;');
                    }
                    catch (Exception $e) {
                        $this->error ($e, 'Could not create new database');
                    }
                    $this->success('Selecting the new database');
                    try {
                        $this->db->exec('USE ' . $this->db_conf->name);
                    }
                    catch (Exception $e){
                        error($e, 'Could not select new database' . $db_conf->name);
                    }
                    $this->success('Creating models and controllers');
                    $models = $this->configuration->models;
                    assert($models !== null);
                    echo '<ul><li>Models were found in the configuration file&hellip;';
                    foreach ($models as $model) {
                        echo '';
                        $this->success('Creating Table for Model <code>' . $model->name . '</code>');
                        $this->create_model_in_db($model);
                        $this->success('Creating PHP classes for Model <code>'. $model->name . '</code>.');
                    }
                    echo '</li>'; // Mddels close

                }
                catch (Exception $e) {
                    $this->error('The database ' . $this->db_conf->name . ' could not be connected', $e->getMessage());
                }
            }
            catch (Exception $e) {
               $this->error('The server could not be connected', $e->getMessage());
            }
        }
    }
}
?>