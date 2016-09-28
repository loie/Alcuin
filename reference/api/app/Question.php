<?php namespace App;

// other models
use App\User;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

    const TYPE = 'question';
    
    protected $fillable = ['title', 'text'];
    protected $guarded = [];
    protected $visible = ['title', 'text'];
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

}