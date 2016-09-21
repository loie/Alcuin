<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TierchenController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create (Request $request) {
        return response()->json(['tierchen' => 'asdfasdf']);
    }

    //
}
