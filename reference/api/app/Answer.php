<?php namespace App;

// other models
use App\User;
use App\Question;
use App\Tag;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model {

    const TYPE = 'question';

    protected $fillable = ['text', 'created', 'edited', 'accepted', 'upvotes', 'downvoted', 'dummy'];
    protected $guarded = [];
    protected $visible = ['text', 'created', 'edited', 'accepted', 'upvotes', 'downvoted', 'dummy'];
    protected $dates = [];

    public static $VALIDATION = [
        'text' => 'required|min:10'
    ];

    public static $RELATIONSHIPS = [
        'belongs_to' => [
            'user' => [
                'id' => 'user'
            ],
            'question' => [
                'id' => 'question'
            ]
        ],
        'has_many' => [],
        'belongs_to_and_has_many' => []
    ];

    public $timestamps = false;

    public function question () {
        return $this->belongsTo('App\Question');
    }

    public function user () {
        return $this->belongsTo('App\User');
    }

}