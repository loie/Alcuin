<?php

class {{model_name}}Model extends AlcuinModel {
    
    private $_belongs_to = array();
    private $_belongs_to_and_has_many = array();
    private $has_many = array();
    
    function __construct($id = null) {
        
    }
    
    public function __set($key, $value) {
        
    }
    
    public function save() {
        // create if not ID
        // update if id exists
    }
    
    public function delete() {
    }
}

?>
