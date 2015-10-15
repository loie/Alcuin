<?php
abstract class Model {

    protected static $PERMISSIONS_TABLE;

    abstract protected static function get_permission_table();
    abstract public static function get_table_name ();
    abstract public static function get_id_column_name ();
    abstract protected static function get_all_properties ();
    abstract public static function get_belongs_to ();
    abstract public static function get_has_many ();
    abstract public static function get_belongs_to_and_has_many ();
    
    /** Returns columns that are allowed for this user for this model for this operation */
    public static function get_properties ($access_type, $user_permissions, $force = false) {
        $model_permissions = static::get_permission_table();
        $allowed_properties = [];
        foreach (static::get_all_properties() as $property) {
            $is_allowed = false;
            $general_allowance = $model_permissions->general->{$access_type};
            $is_allowed = static::check_permission($general_allowance, $user_permissions, $force);
            foreach ($model_permissions->properties as $permission_property) {
                if ($property === $permission_property->name) {
                    $permissions = $permission_property->{$access_type};
                    $is_allowed = static::check_permission($permissions, $user_permissions);
                    break;
                }
                if ($is_allowed && $property === 'id' && ($access_type === Access::UPDATE || $access_type === Access::CREATE)) {
                    $is_allowed = false;
                }
            }
            if ($is_allowed) {
                array_push($allowed_properties, $property);
            }
        }
        return $allowed_properties;
    }

    private static function check_permission($permission, $user_permissions, $force = false) {
        $is_allowed = false;
        if ($force) {
            $is_allowed = true;
        } else {
            if (is_string($permission)) {
                if ($permission === Permissions::ALL) {
                    $is_allowed = true;
                } else if ($permission === Permissions::NONE) {
                    $is_allowed = false;
                } else if ($user_permissions != NULL && is_numeric(array_search($permission, $user_permissions))) {
                    $is_allowed = true;
                }
            } else if (is_array($permission)) {
                if ($user_permissions != NULL) {
                    foreach ($permission as $model_permission) {
                        if (is_numeric(array_search($model_permission, $user_permissions))) {
                            $is_allowed = true;
                            break;
                        }
                    }
                }
            }
        }
        return $is_allowed;
    }


    /**
    *   {
    *        "general": {
    *                "create": "all",
    *                "read": "all",
    *                "update": ["login", "user", "admin"],
    *                "delete": "admin"
    *            }
    *          "columns": [
    *               {
    *                   "name": 'username',
    *                   "create": "all"
    *                   "read": ["user", "admin"],
    *                   "update": "admin",
    *                   "delete": "none"
    *               }, {
    *                   "name": 'tierchen',
    *                   "create": "all"
    *                   "read": ["user", "admin"],
    *                   "update": "admin",
    *                   "delete": "none"
    *               }
    *           ]
    */
    // abstract protected static function get_properties_configuration ();
    // abstract protected static function get_relations ();
    // abstract protected static function get_inferred_permissions ();
    // abstract protected static function is_used_for_auth ();
    // abstract protected static function is_used_for_permission ();

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