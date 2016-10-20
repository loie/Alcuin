<?php

namespace App\Http\Controllers;

class TagController extends Controller
{
    use RESTActions;
    const MODEL = 'App\Tag';
    const TYPE = 'tag';
}
