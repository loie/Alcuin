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
        'belongs_to_and_has_many' => ['users']
    ];

    public $timestamps = false;

    public function users () {
        return $this->belongsToMany('App\User', 'users_roles', 'role_id', 'user_id');
    }
}