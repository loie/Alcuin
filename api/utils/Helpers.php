<?php

class Helpers {
    public static function getNow() {
        return date('Y-m-d H:i:s');
    }

    public static function getDirMapping() {
        return [
            '/[a-zA-Z]+Controller$/' => '/controllers/',
            '/[a-zA-Z]+Model$/' => '/models/',
            '/[a-zA-Z]+View$/' => '/views/'
        ];
    }
}


?>