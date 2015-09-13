<?php
/** This class is used for creating and loading and saving and deleting instances of class Model to the DB */
class ORM {

    public static function create($class_name, $properties) {
        $model = null;
        $model_name = ucfirst($class_name . 'Model');
        if (class_exists($model_name)) {
            $model = new $model_name();
            foreach (get_object_vars($properties) as $key => $value) {
                $model->{$key} = $value;
            }
        } else {
            throw new Exception("Did not find class with class name '" . $class_name . "'.");
        }
        return $model;
    }

    public static function retrieve($class_name, $wheres) {
        $models = [];
        $model_name = ucfirst($class_name . 'Model');
        if (class_exists($model_name)) {
            $model = new $model_name();
            // if ($model->is)
            foreach (get_object_vars($properties) as $key => $value) {
                $model->{$key} = $value;
            }
        } else {
            throw new Exception("Did not find class with class name '" . $class_name . "'.");
        }
        return $models;
    }

    public static function save($models) {
        if (is_array($models)) {

        }
    }

    public static function delete($models) {
        if (is_array($models)) {

        }
    }
}
?>