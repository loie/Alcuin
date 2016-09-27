<?php namespace App;

// other models
use App\User;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

    protected $fillable = ['title', 'text'];
    protected $guarded = [];
    protected $visible = ['title', 'text', 'user', 'tags', 'answers'];
    protected $dates = [];

    public static $VALIDATION = [
        'title' => 'required|min:3|max:255',
        'text' => 'required|min:10'
    ];

    public static $RELATIONSHIPS = [
        'belongs_to' => ['user'],
        'has_many' => ['answers'],
        'has_and_belongs_to_many' => ['tags']
    ];

    public $timestamps = false;

    public function answers () {
        return $this->hasMany('App\Answer');
    }

    public function user () {
        return $this->belongsTo('App\User');
    }

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