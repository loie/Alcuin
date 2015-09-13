<?php

abstract class Controller {

    protected static function getSalt() {
        return [2, 3, 5, 7, 8, 13, 14, 20];
    }

    public function __call($name, $arguments) {

        if (sizeof($arguments) !== 1) {
            throw new Exception ("Expecting exactly one arguemnt of type 'Request'. Found wrong numbers of rguments: ". $var_dump($arguments));
        }
        else {
            $result = $this->{$name}($arguments[0]);
        }
        $this->show_in_view($result);
    }

    abstract protected function get_action($request);

    abstract protected function post_action($request);

    abstract protected function put_action($request);

    abstract protected function delete_action($request);

    private function show_in_view($data) {
        $view = new JsonView($data);
        $view->render($data);
    }

    protected function create_error($errorMessage) {
        $message = new stdClass();
        $message->error = $errorMessage;
        return $message;
    }
}

?>