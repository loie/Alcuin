<?php

class SessionsModel extends Model {

    public static function get_table_name () {
        return 'users';
    }

    public static function get_id_column_name () {
        return 'user';
    }

    protected static function get_all_properties () {
        return ['id', 'token', 'token_last_updated'];
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
                "name": "role",
                "model": "role",
                "via_table": "users_roles",
            }
        ]';

        $relations = json_decode($relations_json);
        return $relations;
    }

    protected static function get_permission_table () {
        if (static::$PERMISSIONS_TABLE == NULL) {
            $permission_table = '{
                "general": {
                    "create": "login",
                    "read": "all",
                    "update": ["self", admin"],
                    "delete": ["self", "admin"]
                },
                "properties": [{
                    "name": "token",
                    "read": ["self", "admin"],
                    "update": ["self", "admin"]
                }, {
                    "name": "token_last_updated",
                    "read": ["self", "admin"],
                    "update": ["admin", "self"]
                }]
            }';
            static::$PERMISSIONS_TABLE = json_decode($permission_table);
        }
        return static::$PERMISSIONS_TABLE;
    }


}

?>