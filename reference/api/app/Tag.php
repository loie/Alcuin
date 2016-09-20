
<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {

    protected $fillable = ["name"];

    // protected $dates = ["due"];

    public static $rules = [
        "name" => "required",
    ];

    public $timestamps = false;

}