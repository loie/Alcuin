<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    use RESTActions;
    const MODEL = 'App\User';
    const TYPE = 'user';

    public function create (Request $request) {
        $email = $request->has('email') ? $request->input('email') : null;
        $password_hash = $request->has('password_hash') ? $request->input('password_hash') : null;
        $name = $request->has('name') ? $request->input('name') : null;
        $m = self::MODEL;
        $this->validate($request, $m::$VALIDATION);

        $user = new User;
        $user->email = $email;
        $user->password = hash('sha256', self::spice($password_hash));
        $user->name = $name;
        $user->save();

        $value = [
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ],
            'links' => [
                'self' => $request->fullUrl() . '/' . $user->id
            ],
            'relationships' => [
                'token' => [
                ]
            ]
        ];
        return response()->json($value, 201);
    }
    //
}
