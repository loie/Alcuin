<?php

namespace App;

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
    protected $fillable = [
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'token',
        'timestamp'
    ];

    public function roles () {
        return $this->belongsToMany('App\Role', 'users_roles', 'user_id', 'role_id');
    }


    public function answers () {
        return $this->hasMany('App\Answer');
    }

    public function user () {
        return $this->belongsTo('App\User');
    }

    public $timestamps = false;

}
