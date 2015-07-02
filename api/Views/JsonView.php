<?php

class JsonView extends ApiView {
    public function render($content) {
        header('Content-Type: application/vnd.api+json');
        echo json_encode($content);
        return true;
    }
}

?>