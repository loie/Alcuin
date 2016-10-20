<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    protected static $SALT = [1, 2, 4, 5, 6, 9, 10, 13, 15, 18, 22];
    const PEPPER = '___[]+++';

    const ALL = 'all';
    const MY = 'self';
    const NONE = 'none';


    protected static function spice ($string) {
        $i = 0;
        $stringLength = strlen($string);
        $spiced = $string;
        foreach (self::$SALT as $index) {
            if ($index < $stringLength) {
                $spiced .= substr($string, $index, 1);
            }
        }
        $spiced = self::PEPPER . $spiced . self::PEPPER;
        return $spiced;
    }
}
