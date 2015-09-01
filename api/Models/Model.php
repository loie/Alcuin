<?php

abstract class Model {

    abstract protected static function get_table_name ();
    abstract protected static function get_properties ();
    abstract protected static function get_inferred_permissions ();

    public function __construct ($auth_token, $id) {
        if 
    }

    public function save () {
        // if new, use create, else use update
    }

    public function delete () {
        // if new, just unset, else use delete
    }

    protected function create () {
        // POST
    }

    protected function read () {
        // get
    }

    protected function update () {
        // put
    }

    protected function delete () {

    }
}

?>