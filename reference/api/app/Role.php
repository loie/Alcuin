<?php namespace App;

// other models
use Illuminate\Http\Request;
use App\User;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    const TYPE = 'role';

    protected $hidden = ['users', 'pivot'];
    protected $dates = [];

    public static function VALIDATION (Request $request) {
        $validation = [
            'name' => 'required|min:1|max:255'
        ];
        return $validation;
    }

    public static $PROPERTIES = [
        'type',
    ];

    public static $PROPERTIES_PERMISSIONS = [
        'type' => [
            'create' => ['admin'],
            'read' => ['admin'],
            'update' => ['admin']
        ]
    ];


    public static $RELATIONSHIPS = [
        'belongs_to_and_has_many' => ['users']
    ];

    public $timestamps = false;

    public function users () {
        return $this->belongsToMany('App\User', 'users_roles', 'role_id', 'user_id');
    }
}