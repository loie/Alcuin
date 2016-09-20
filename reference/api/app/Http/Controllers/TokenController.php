<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Routing\Controller;

use App\User;

class TokenController extends Controller
{
    // use RESTAction;


    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     //
    // }

    public function create (Request $request) {
        // echo hash('sha256', 'lorenz.merdian@googlemail.combernie');
        $email = $request->hasHeader('X-email') ? $request->header('X-email') : null;
        $password_hash = $request->hasHeader('X-password-hash') ? $request->header('X-password-hash') : null;
        if (is_string($email) && is_string($password_hash)) {
            $spiced_password_hash = hash('sha256', self::spice($password_hash));
            try {
                $user = User::where(
                    [
                        'email' => $email,
                        'password' => $spiced_password_hash
                    ])->firstOrFail();
                $timestamp = date('Y-m-d H:i:s');
                $user->timestamp = $timestamp;
                $user->token = hash('sha256', $spiced_password_hash . $timestamp);
                $user->save();
                $value = [
                    'data' => [
                        'id' => $user->id,
                        'token' => $user->token,
                        'timestamp' => $user->timestamp
                    ],
                    'relationships' => [
                        'user' => [
                            'data' => [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'token' => $user->token
                            ]
                        ]
                    ]
                ];
                return response()->json($value, 201);

            } catch (ModelNotFoundException $e) {
                abort(404, 'No user using ' . $email . ' found');
            }
        } else {
            throw abort(401, 'No credentials have been sent');
        }
    }

    //
}
