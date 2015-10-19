<?php

class Helpers {
    public static function getNow () {
        return date('Y-m-d H:i:s');
    }

    public static function getDirMapping () {
        return [
            '/[a-zA-Z]+Controller$/' => '/controllers/',
            '/[a-zA-Z]+Model$/' => '/models/',
            '/[a-zA-Z]+View$/' => '/views/'
        ];
    }

    // Get Salt array from configuration.json
    public static function getSalt () {
        return [2, 3, 5, 7, 8, 13, 14, 20];
    }

    // convenience method for salting and hashing given strings
    public static function hash ($string) {
        if ($string === NULL) {
            throw new Exception ('Cannot salt and hash NULL or an empty string');
        }
        $string_salted = $string;
        foreach (static::getSalt() as $index) {
            if (mb_strlen($string_salted) > $index) {           
                $string_salted .= substr($string_salted, $index, 1);
            }
        }
        $hashed = sha1(str_rot13($string_salted));
        return $hashed;
    }
}


?>