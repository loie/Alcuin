<?php

class Installation {
    public static $CONFIG_FILE = 'configuration.json';
    private $configuration;
    
    void __construct() {
        if (file_exists(self::$CONFIG_FILE)) {
            $json = file_get_content(self::$CONFIG_FILE);
            $this->configuration = json_decode($json);
        }
        else {
            echo 'ERROR: No configuration file found!';
        }
    }
}
?>
