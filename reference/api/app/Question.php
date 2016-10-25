<?php namespace App;

// other models
use Illuminate\Http\Request;
use App\User;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

    const TYPE = 'question';

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
            'create' => ['admin'],
            'read' => ['all'],
            'update' => ['self', 'admin']
        ],
        'text' => [
            'create' => ['admin'],
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

    public static $RELATIONSHIP_PERMISSIONS = [
        'user' => [
            'create' => ['admin'],
            'read' => ['self'],
            'delete' => ['admin']
        ],
        'answers' => [
            'create' => ['self'],
            'read' => ['self'],
            'delete' => ['none']
        ],
        'tags' => [
            'create' => ['admin'],
            'read' => ['self'],
            'delete' => ['admin']
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