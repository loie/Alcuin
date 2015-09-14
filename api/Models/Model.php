<?php
abstract class Model {

    abstract protected static function get_table_name ();
    // abstract protected static function get_properties_configuration ();
    // abstract protected static function get_relations ();
    // abstract protected static function get_inferred_permissions ();
    // abstract protected static function is_used_for_auth ();
    // abstract protected static function is_used_for_permission ();
    public static function get_column_filter () {
        return [];
    }

    // public function __construct ($properties, $relations) {
    //     $this->model_object->type = self::get_table_name();
    //     $this->model_object->attributes = new stdClass();
    //     // set initial values
    //     $properties_configuration = $this->get_properties_configuration();
    //     $this->model_object->id = NULL;
    //     if ($properties !== NULL) {
    //         foreach ($properties_configuration as $configuration) {
    //             $value = isset($properties->{$configuration->name}) ? $properties->{$configuration->name} : NULL;
    //             $this->model_object->attributes->{$configuration->name} = $value;
    //         }
    //     }
    //     $relations = $this->get_relations();
    //     foreach ($relations->belongs_to as $parent) {
    //         $parent_object = new stdClass();
    //         $vars = get_object_vars($parent);
    //         echo '<pre>' . $vars . '</pre>';
    //         $this->model_object->{$parent->name}
    //     }
    // }
    //         // foreach ($relations->has_many as $child) {

            // }
            // foreach ($relations->belongs_to_and_has_many as $sibling) {

            // }
}

?>