<?php
class {{model_name}}sController extends MyController
{

    // public function __callfunction($name, $params) {
    //      $result = $this->{blAfunctionName}($params);
    //      $this->show_in_view()  
    // }

    private function get_action($request) {
        if(isset($request->url_elements[2])) {
            $user_id = (int)$request->url_elements[2];
            if(isset($request->url_elements[3])) {
                switch($request->url_elements[3]) {
                case 'friends':
                    $data["message"] = "user " . $user_id . "has many friends";
                    break;
                default:
                    // do nothing, this is not a supported action
                    break;
            } else {
                $data["message"] = "here is the info for user " . $user_id;
            }
        } else {
            $data["message"] = "you want a list of users";
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
        $model = new {{model_name}}Model();
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