<?php

require 'Base_Model.php';

class User extends Base_Model {

    protected static TABLE_NAME = 'users';
    protected static COLUMNS = ['email', 'password', 'name', 'token'];
    protected static VALIDATION = [
        'email' => /^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD
    ];
    protected static PERMISSIONS = [
        'entity' => [
            'create' => [],
            'read' => [],
            'update' => [],
            'delete' => []
        ],
        'columns' => [
            'read' => [],
            'update' => []
        ]
    ];

    private $current = new stdClass();
    private $original = new stdClass();

    public function __construct ($connection, $desc = null) {
        assert($connection != null);
        if ($id === null) {
            return $this;
        } else if (is_string($desc) or is_numeric($desc)) {
            $id = $desc;

        } else if (is_array($desc)) {

        }
    }

    public function __set ($name, $new_value) {
        // check existence of columns
        assert(array_key_exists($name, $this->columns));
        // validation

        // check permission
        $this->inner->{$name} = $new_value;
    }

    public function __get ($name) {
        assert($name !== null);
        // check existence
        assert(array_key_exists(User::COLUMNS, $name));
        // check permission
        return $this->inner->{$name};
    }

    public function save () {

    }

    private function is_pristine () {

    }


}
?>