<?php
include 'Configuration.php';
include 'Architecture/Property.php';
include 'DB/DB_Schema_Creator.php';

class Main {
    private $db = null;
    private $db_conf = null;
    private $config_file = null;
    private $configuration = null;

    public function __construct($config_file) {
        $this->config_file = $config_file;
        echo '<li>Searching for configuration file <code>' . $this->config_file . '</code>&hellip; ';
        if (file_exists($config_file)) {
            $this->success('Trying to parse configuration file <code>' . $config_file . '</code>');
            try {
                // create configuration from file
                $this->configuration = new Configuration($config_file);
                assert($this->configuration !== null);
                $this->success('Checking for database settings');
                $this->db_conf = $this->configuration->db;
                if ($this->db_conf === null) {
                    $this->error('The file <code>'.$config_file.'</code> has not defined a database configuration');
                } else {
                    $this->success();
                }
            }
            catch (Exception $e) {
                $this->error('The file <code>'.$this->config_file.'</code> is not in valid YAML format');
            }
        } else {
            $this->error('The file <code>'.$config_file.'</code> does not exists. Have you forgotten to create it?');
        }
    }



    private function create_model_in_filesystem($model) {
        $this->success('Creating Model class for ' . $model);
    }

    private function create_controller_in_filesystem($model) {
        $this->success('Creating controller class for ' . $model);
    }


    public function create_db_schema () {

    }

    public function create_rest_service () {

    }
}
?>