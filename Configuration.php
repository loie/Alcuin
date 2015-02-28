<?php

class Configuration {
    public static $CONFIG_FILE = 'configuration.json';
    private $configuration;
    
    void __construct($file) {
        $this->load_config($file);
    }
    
    private function load_config ($file) {
        assert(file_exists($file));
        $json = file_get_content($file);
        $this->configuration = json_decode($json);
        if ($this->configuration == null) {
            $this->configuration = null;
            throw new Exception($file . 'is not a valid JSON file');
        }
        assert(get_class($this->configuration) === 'stdClass');
    }
    
    public void __set ($name, $value) {
        throw new Exception("Configuration is read-only");
    }
    
    public void __get ($name, $value) {
        assert($this->configuration !== null);
        return $this->configuration->{$name};
    }
}
?>
