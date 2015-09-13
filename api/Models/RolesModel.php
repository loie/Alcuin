<?php

class RolesModel extends Model {


    private static $INFERRED_PERMISSIONS_PROPS;

    public static function get_inferred_permissions () {
        return RolesModel::$INFERRED_PERMISSIONS_PROPS;
    }

    protected static function set_inferred_permissions ($map) {
        Model::$INFERRED_PERMISSIONS_PROPS = $map;
    }

    static {
        $map = [
            "all" => [],
            "admin" => [
                "create" => ["name"],
                "read" => ["name"],
                "update" => ["name"],
                "delete" => ["name"],
            ],
            "user" => [],
            "login" => []
        ];
        $self::set_inferred_permissions($map);
    }
}

?>