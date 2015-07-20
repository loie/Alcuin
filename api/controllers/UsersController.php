<?php
class UsersController extends AuthController {

    const TABLE_NAME = 'users';

    public function __call($name, $arguments) {
        parent::__call($name, $arguments);
        $result = null;
        if (sizeof($arguments) === 1) {
            $result = $this->{$name}($arguments[0]);
        } else {
            $result = $this->{$name}($arguments);
        }
        $this->show_in_view($result);
    }

    protected function get_permissions () {
        $permissions = [
            'bla' => [
                'read' => 'asd'
            ],
            'blupp' => [],
            'all' => [
                'create' => 'asdfsd',
                'read' => 'full_name'
            ]
        ];
        return $permissions;
    }

    protected function get_roles () {
        $roles = ['all'];

    }

    public function get_current_permission () {

    }

    protected function get_column_names_by_permission () {
        return ["none" => array('full_name')];
                //     "name": "email",
                //     "type": "string",
                //     "max_length": 255,
                //     "use_as_id": true,
                //     "permissions": {
                //         "read": "none"
                //     },
                //     "validation": "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?"
                // },
                // {
                //     "name": "password",
                //     "type": "hash",
                //     "use_as_password": true,
                // },
                // {
                //     "name": "full_name",
                //     "type": "string",
                //     "max_length": 255,
                //     "null_allowed": true
                // }
    }

    // perform action for GET
    private function get_action($request) {
        $response = array();
        // Has ID
        if (isset($request->url_elements[2])) {
            $user_id = (int)$request->url_elements[2];
            if(isset($request->url_elements[3])) {
                // get subitems
                switch($request->url_elements[3]) {
                    case 'friends':
                        $response["message"] = "user " . $user_id . "has many friends";
                        break;
                    default:
                        // do nothing, this is not a supported action
                        break;
                }
            } else {
                // infos about one object
                $response["message"] = "here is the info for user " . $user_id;
                $response['meta'] = array('success' => true);
            }
        } else {
            // get all items
            $data = array();
            $query = "SELECT " . $this->get_readable_columns() . ' FROM `' . $this->table_name . '` WHERE 1';
            DB::

            $response['data'] = $data;
            
            $response["message"] = "you want a list of users";
            $response['meta'] = array('success' => true, 'total' => 12);
        }
        return $response;
    }


    private function post_action($request) {
        // update model
        $data = $request->parameters;
        $data['message'] = "This data was submitted";
        return $data;
    }

    private function put_action($request) {
        // create model
        $model = new Role();
        assert($model != null);
        foreach ($request as $key => $value) {
            $model->{$key} = $value;
        }
        return $model;
    }

    private function delete_action($request) {

    }

    private function show_in_view($data) {
        $view = new JsonView($data);
        $view->render($data);
    }
}
?>
