<?php namespace App;

// other models
use Illuminate\Http\Request;
use App\User;
use App\Question;
use App\Tag;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model {

    const TYPE = 'answer';

    protected $guarded = [];
    protected $hidden = ['pivot'];
    protected $dates = [];

    public static function VALIDATION (Request $request) {
        $validation = [
            'text' => 'required|min:10'
        ];
        return $validation;
    }

    public static $PROPERTIES = ['text', 'created', 'edited', 'accepted', 'upvotes', 'downvotes', 'dummy'];

    public static $PROPERTIES_PERMISSIONS = [
        'text' => [
            'create' => ['all'],
            'read' => ['all'],
            'update' => ['admin']
        ],
        'created' => [
            'create' => ['admin'],
            'read' => ['admin'],
            'update' => ['admin']
        ],
        'edited' => [
            'create' => ['admin'],
            'read' => ['admin'],
            'update' => ['admin']
        ],
        'accepted' => [
            'create' => ['admin'],
            'read' => ['admin'],
            'update' => ['admin']
        ],
        'upvotes' => [
            'create' => ['admin'],
            'read' => ['admin'],
            'update' => ['admin']
        ],
        'downvotes' => [
            'create' => ['admin'],
            'read' => ['admin'],
            'update' => ['admin']
        ],
        'dummy' => [
            'create' => ['admin'],
            'read' => ['admin'],
            'update' => ['admin']
        ]
    ];

    public static $RELATIONSHIPS = [
        'belongs_to' => [
            'user' => [
                'type' => 'user'
            ],
            'question' => [
                'type' => 'question'
            ]
        ],
        'has_many' => [],
        'belongs_to_and_has_many' => []
    ];

    public static $RELATIONSHIP_PERMISSIONS = [
        'user' => [
            'create' => ['admin'],
            'read' => ['all'],
            'delete' => ['admin']
        ],
        'question' => [
            'create' => ['self'],
            'read' => ['all'],
            'delete' => ['none']
        ]
    ];

    public $timestamps = false;


    public function question () {
        return $this->belongsTo('App\Question');
    }

    public function user () {
        return $this->belongsTo('App\User');
    }

}