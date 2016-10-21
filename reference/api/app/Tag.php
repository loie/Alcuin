<?php namespace App;

// other models
use Illuminate\Http\Request;
use App\User;
use App\Question;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {

    protected $guarded = [];
    protected $hidden = ['questions', 'answers', 'pivot'];
    protected $dates = [];

    public static function VALIDATION (Request $request) {
        $validation = [
            'name' => 'required|min:1|max:255'
        ];
        return $validation;
    }

    public static $PROPERTIES = [
        'name',
    ];

    public static $PROPERTIES_PERMISSIONS = [
        'name' => [
            'read' => ['all'],
            'update' => ['none']
        ]
    ];

    public static $RELATIONSHIPS = [
        'belongs_to' => [],
        'has_many' => [],
        'belongs_to_and_has_many' => [
            'questions' => [
                'type' => 'question'
            ]
        ]
    ];

    public $timestamps = false;

    public function questions () {
        return $this->belongsToMany('App\Question', 'questions_tags', 'tag_id', 'question_id');
    }

    public function answers () {
        return $this->belongsToMany('App\Answer', 'answers_tags', 'tag_id', 'answer_id');
    }
}