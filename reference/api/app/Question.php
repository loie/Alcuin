<?php namespace App;

// other models
use Illuminate\Http\Request;
use App\User;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

    protected $fillable = ['title', 'text'];
    protected $guarded = [];
    protected $hidden = ['answers', 'tags', 'user', 'pivot'];
    protected $dates = [];

    public static $VALIDATION = [
    ];

    public static function VALIDATION (Request $request) {
        $validation = [
            'title' => 'required|min:3|max:255',
            'text' => 'required|min:10'
        ];
        return $validation;
    }

    public static $PROPERTIES = [
        'title',
        'text',
    ];

    public static $PROPERTIES_PERMISSIONS = [
        'title' => [
            'read' => ['all'],
            'update' => ['self']
        ],
        'text' => [
            'read' => ['admin'],
            'update' => ['admin']
        ]
    ];

    public static $RELATIONSHIPS = [
        'belongs_to' => [
            'user' => [
                'type' => 'user'
            ]
        ],
        'has_many' => [
            'answers' => [
                'type' => 'answer'
            ]
        ],
        'belongs_to_and_has_many' => [
            'tags' => [
                'type' => 'tag'
            ]
        ]
    ];

    public $timestamps = false;

    public function answers () {
        return $this->hasMany('App\Answer');
    }

    public function tags () {
        return $this->belongsToMany('App\Tag', 'questions_tags', 'question_id', 'tag_id');
    }

    public function user () {
        return $this->belongsTo('App\User');
    }

}