<?php

class SessionsModel extends Model {

    public static function get_table_name () {
        return 'users';
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
            "roles": "users_roles"
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
                    "update": ["login", "admin"],
                    "delete": ["login", "admin"]
                },
                "properties": [{
                    "name": "token",
                    "read": ["login", "admin"],
                    "update": ["login", "admin"]
                }, {
                    "name": "token_last_updated",
                    "read": ["login", "admin"],
                    "update": ["admin", "login"]
                }]
            }';
            static::$PERMISSIONS_TABLE = json_decode($permission_table);
        }
        return static::$PERMISSIONS_TABLE;
    }


}

?>