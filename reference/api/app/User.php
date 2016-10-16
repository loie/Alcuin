<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = ['email', 'name'];


    public static function VALIDATION (Request $request, $id = null) {
        $validation = [
            'email' => 'required|email|unique:users,email',
            'password_hash' => 'required|max:64|min:64'
        ];
        if (!is_null($id)) {
            $class = self::class;
            $model = $class::find($id);
            $validation['email'] .= ",{$model->email},email";;
        }
        return $validation;
    }

    public static $PROPERTIES = [
        'email',
        'name',
        'password',
        'token',
        'expires'
    ];

    public static $PROPERTIES_PERMISSIONS = [
        'email' => ['self'],
        'name' => ['all'],
        'password' => ['none'],
        'token' => ['admin'],
        'expires' => ['self', 'admin']

    ];

    public static $RELATIONSHIPS = [
        'has_many' => [
            'questions' => [
                'type' => 'question'
            ],
            'answers' => [
                'type' => 'answer'
            ]
        ],
        'belongs_to' => [],
        'belongs_to_and_has_many' => [
            'roles' => [
                'type' => 'role'
            ]
        ]
    ];

    public function roles () {
        return $this->belongsToMany('App\Role', 'users_roles', 'user_id', 'role_id');
    }

    public function questions () {
        return $this->hasMany('App\Question');
    }

    public function answers () {
        return $this->hasMany('App\Answer');
    }

    public $timestamps = false;

}
