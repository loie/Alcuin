<?php

class Configuration {
    private $configuration;
    
    function __construct ($file) {
        $this->load_config($file);
    }
    
    private function load_config ($file) {
        assert(file_exists($file));
        $this->configuration = json_decode(json_encode(spyc_load_file($file)));
        if ($this->configuration === null) {
            $this->configuration = null;
            throw new Exception($file . 'is not a valid YAML file');
        }
        assert(get_class($this->configuration) === 'stdClass');
    }
    
    public function __set ($name, $value) {
        throw new Exception("Configuration is read-only");
    }
    
    public function __get ($name) {
        assert($this->configuration !== null);
        return $this->configuration->{$name};
    }
}
?>
