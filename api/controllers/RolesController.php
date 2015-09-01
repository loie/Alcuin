<?php
class RolesController extends DBController {

    private $

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

    // perform action for GET
    private function get_action($request) {
        $stmt = $dbh->prepare("SELECT INTO REGISTRY (name, value) VALUES (:name, :value)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':value', $value);
        if (isset($request->url_elements[2])) {
            $user_id = (int)$request->url_elements[2];
            if(isset($request->url_elements[3])) {
                switch($request->url_elements[3]) {
                    case 'friends':
                        $data["message"] = "user " . $user_id . "has many friends";
                        break;
                    default:
                        // do nothing, this is not a supported action
                        break;
                }
            } else {
                $data["message"] = "here is the info for user " . $user_id;
                $data['meta'] = array('success' => true);
            }
        } else {
            $data["message"] = "you want a list of users";
            $data['meta'] = array('success' => true, 'total' => 12);
        }
        return $data;
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
