<?php

class RolesModel extends Model {

public static function get_table_name () {
        return 'roles';
    }

    public static function get_id_column_name () {
        return 'role';
    }

    protected static function get_all_properties () {
        return ['id', 'type'];
    }

    public static function get_belongs_to () {
        return NULL;
    }
    public static function get_has_many () {
        return NULL;
    }
    public static function get_belongs_to_and_has_many () {
        $relations_json = '[
            {
                "name": "user",
                "model": "user",
                "via_table": "users_roles"
            }
        ]';

        $relations = json_decode($relations_json);
        return $relations;
    }

    protected static function get_permission_table () {
        if (static::$PERMISSIONS_TABLE == NULL) {
            $permission_table = '{
                "general": {
                    "create": "admin",
                    "read": "all",
                    "update": "admin",
                    "delete": "admin"
                }
            }';
            static::$PERMISSIONS_TABLE = json_decode($permission_table);
        }
        return static::$PERMISSIONS_TABLE;
    }
}

?>