<?php namespace App;

// other models
use App\User;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    protected $fillable = ['name'];
    protected $guarded = [];
    protected $visible = ['name'];
    protected $dates = [];

    public static $VALIDATION = [
        'type' => 'required|min:3|max:255',
    ];

    public static $RELATIONSHIPS = [
        'has_and_belongs_to_many' => ['users']
    ];

    public $timestamps = false;

    public function users () {
        return $this->belongsToMany('App\User', 'users_roles', 'role_id', 'user_id');
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