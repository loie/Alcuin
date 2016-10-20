<?php

namespace App\Http\Controllers;

class QuestionController extends Controller
{
    use RESTActions;
    const MODEL = 'App\Question';
    const TYPE = 'question';
}
