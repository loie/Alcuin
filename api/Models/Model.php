<?php
require '../DB.php';

abstract class Model {

    private $model_object = new stdClass();

    abstract protected static function get_table_name ();
    abstract protected static function get_properties_configuration ();
    abstract protected static function get_relations ();
    abstract protected static function get_inferred_permissions ();

    public function __construct ($auth_token, $id = NULL, $properties, $relations) {
        $this->model_object->type = self::get_table_name();
        $this->model_object->attributes = new stdClass();
        // set initial values
        $properties_configuration = $this->get_properties_configuration();
        if ($id === NULL) {
            // create
            $this->model_object->id = NULL;
            if ($properties !== NULL) {
                foreach ($properties_configuration as $configuration) {
                    $value = isset($properties->{$configuration->name}) ? $properties->{$configuration->name} : NULL;
                    $this->model_object->attributes->{$configuration->name} = $value;
                }
            }
            $relations = $this->get_relations();
            // foreach ($relations->belongs_to as $parent) {
            //     $parent_object = new stdClass();
            //     $parent_object-> 
            //     $this->model_object->{$parent->name}
            // }
            // foreach ($relations->has_many as $child) {

            // }
            // foreach ($relations->belongs_to_and_has_many as $sibling) {

            // }

        } else {
            // get from DB
            $this->model_object->id = $id;
            // set attributes
            $properties_configuration
            
        }
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