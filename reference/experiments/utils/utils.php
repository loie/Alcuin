<?php
    function getNow () {
        return date('Y-m-d H:i:s');
    }

    function getDirMapping () {
        return ['/[a-zA-Z]$/' => '/models/'];
            // '/[a-zA-Z]+Controller$/' => '/controllers/',
            // '/[a-zA-Z]+Model$/' => '/models/',
            // '/[a-zA-Z]+View$/' => '/views/'
    }

    // Get Salt array from configuration.json
    function getSalt () {
        return [2, 3, 5, 7, 8, 13, 14, 20];
    }

    // convenience method for salting and hashing given strings
    function hashme ($string, $salted = true, $rotated = true) {
        if ($string === NULL) {
            throw new Exception ('Cannot salt and hash NULL or an empty string');
        }
        $string_salted = $string;
        if ($salted) {
            foreach (static::getSalt() as $index) {
                if (mb_strlen($string_salted) > $index) {           
                    $string_salted .= substr($string_salted, $index, 1);
                }
            }
        }
        $hashed = null;
        if ($rotated) {
            $hashed = hash('sha256', str_rot13($string_salted));
        } else {
            $hashed = hash('sha256', $string_salted);
        }
        return $hashed;
    }

    function get_model ($model_name, $desc, $connection) {
    }

    function create_model () {

    }

?>