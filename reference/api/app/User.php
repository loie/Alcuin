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

    const TYPE = 'user';

    public function __construct (array $attributes = []) {
        parent::__construct($attributes);
        $this->fillable(self::$PROPERTIES);
    }


    public static function VALIDATION (Request $request, $model = null) {
        $validation = [
            'name' => 'max:255|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|max:64|min:64'
        ];
        if (!is_null($model)) {
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
        'email' => [
            'create' => ['admin'],
            'read' => ['self'],
            'update' => ['self', 'admin']
        ],
        'name' => [
            'create' => ['admin'],
            'read' => ['all'],
            'update' => ['admin']
        ],
        'password' => [
            'create' => ['admin'],
            'read' => ['none'],
            'update' => ['admin']
        ],
        'token' => [
            'create' => ['admin'],
            'read' => ['none'],
            'update' => ['admin', 'self']
        ],
        'expires' => [
            'create' => ['admin'],
            'read' => ['self', 'admin'],
            'update' => ['admin']
        ]
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

    public static $RELATIONSHIP_PERMISSIONS = [
        'roles' => [
            'create' => ['admin'],
            'read' => ['all'],
            'delete' => ['admin']
        ],
        'questions' => [
            'create' => ['user'],
            'read' => ['self'],
            'delete' => ['self']
        ],
        'answers' => [
            'create' => ['none'],
            'read' => ['none'],
            'delete' => ['none']
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
