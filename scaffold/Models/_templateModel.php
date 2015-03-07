<?php

class {{model_name}}Model extends AlcuinModel {
    
    private $_belongs_to = array();
    private $_belongs_to_and_has_many = array();
    private $has_many = array();
    
    public function get($id) {
      if (isset($id)) {
        // GET on One element
      } else {
        // LIST all elements
      }
    }
    
    public function post($id, $content) {
        if (isset($id)) {
            // POST on one elelment
        } else {
            
        }
    }
    
    public function put($id, $content) {
        if (isset($id)) {
            // POST on one elelment
        } else {
            
        }
    }
    
    public function delete($id, $content) {
        if (isset($id)) {
            // POST on one elelment
        } else {
            
        }
    }
}

?>
