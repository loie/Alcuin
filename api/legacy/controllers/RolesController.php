<?php
require ('Controller.php');

class RolesController extends Controller {

    protected function get_action($request) {
        
    }

    // // perform action for GET
    // private function get_action($request) {

    //     $stmt = $dbh->prepare("SELECT INTO REGISTRY (name, value) VALUES (:name, :value)");
    //     $stmt->bindParam(':name', $name);
    //     $stmt->bindParam(':value', $value);
    //     if (isset($request->url_elements[2])) {
    //         $user_id = (int)$request->url_elements[2];
    //         if(isset($request->url_elements[3])) {
    //             switch($request->url_elements[3]) {
    //                 case 'friends':
    //                     $data["message"] = "user " . $user_id . "has many friends";
    //                     break;
    //                 default:
    //                     // do nothing, this is not a supported action
    //                     break;
    //             }
    //         } else {
    //             $data["message"] = "here is the info for user " . $user_id;
    //             $data['meta'] = array('success' => true);
    //         }
    //     } else {
    //         $data["message"] = "you want a list of users";
    //         $data['meta'] = array('success' => true, 'total' => 12);
    //     }
    //     return $data;
    // }

    // private function post_action($request) {
    //     // update model
    //     $data = $request->parameters;
    //     $data['message'] = "This data was submitted";
    //     return $data;
    // }

    // private function put_action($request) {
    //     // create model
    //     $model = new Role();
    //     assert($model != null);
    //     foreach ($request as $key => $value) {
    //         $model->{$key} = $value;
    //     }
    //     return $model;
    // }

    // private function delete_action($request) {

    // }

    
}
?>
