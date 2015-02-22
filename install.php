<?php

class Installation {
    public static $CONFIG_FILE = 'configuration.json';
    private $configuration;
    
    void __construct() {
        $this->load_config();
    }
    
    private function load_config () {
        assert(file_exists(self::$CONFIG_FILE));
        $json = file_get_content(self::$CONFIG_FILE);
        assert($json !== null);
        $this->configuration = json_decode($json);
        assert(get_class($this->configuration) === 'stdClass');
    }
}
?>
