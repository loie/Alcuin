<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

    const CREATION_ACTION = 'update-question';

    protected $fillable = [];

    protected $dates = [];

    public static $VALIDATION = [
        'title' => 'required|min:3|max:255',
        'text' => 'required|min:10'
    ];

    public $timestamps = false;

    // public static $rules = [
    //     "name" => "required",
    //     "age" => "integer|min:13",
    //     "email" => "email|unique:users,email_address",
    // ];

    // Relationships

    // public function project()
    // {
    //     return $this->belongsTo("App\Project");
    // }

    // public function accounts()
    // {
    //     return $this->hasMany("Tests\Tmp\Account");
    // }

    // public function owner()
    // {
    //     return $this->belongsTo("App\User");
    // }

    // public function number()
    // {
    //     return $this->hasOne("Tests\Tmp\Phone");
    // }

    // public function tags()
    // {
    //     return $this->belongsToMany("Tests\Tmp\Tag")->withTimestamps();
    // }

}