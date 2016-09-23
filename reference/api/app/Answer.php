<?php namespace App;

// other models
use App\User;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

    const CREATION_ACTION = 'update-question';

    protected $fillable = ['title', 'text'];
    protected $guarded = [];
    protected $visible = ['text', 'created', 'edited', 'accepted', 'upvotes', 'downvoted', 'dummy', 'user', 'question'];
    protected $dates = [];

    public static $VALIDATION = [
        'text' => 'required|min:10'
    ];

    public static $RELATIONSHIPS = [
        'belongs_to' => ['user', 'answer'],
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