<?php namespace App;

// other models
use App\User;
use App\Question;
use App\Answer;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {

    protected $fillable = ['name'];
    protected $guarded = [];
    protected $visible = ['name'];
    protected $dates = [];

    public static $VALIDATION = [
        'name' => 'required|min:1|max:255'
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