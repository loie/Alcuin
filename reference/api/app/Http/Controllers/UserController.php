<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Routing\Controller;

use App\User;
use RESTAction;

class UserController extends Controller
{

    private static $VALIDATION = [
        'email' => 'required|unique:users|email',
        'password_hash' => 'required|max:64|min:64'
    ];


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
        $email = $request->has('email') ? $request->input('email') : null;
        $password_hash = $request->has('password_hash') ? $request->input('password_hash') : null;
        $name = $request->has('name') ? $request->input('name') : null;

        $this->validate($request, self::$VALIDATION);

        $user = new User;
        $user->email = $email;
        $user->password = hash('sha256', self::spice($password_hash));
        $user->name = $name;
        $user->save();
    }

    public function read ($id) {
        $questions = App\Question::all();
        foreach ($questions as $question) {
            echo $question->title;
        }
    }

    //
}
