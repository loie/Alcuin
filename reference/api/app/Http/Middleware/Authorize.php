<?php

namespace App\Http\Middleware;

use Closure;
use App\Question as Question;
use App\Answer as Answer;
use App\Role as Role;
use App\User as User;
use App\Tag as Tag;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authorize
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = $request->user();
        $gateName = null;
        switch ($request->method()) {
            case 'GET':
                $gateName = 'view';
                break;
            case 'POST':
                $gateName = 'create';
                break;
            case 'PUT':
                $gateName = 'update';
                break;
            case 'DELETE':
                $gateName = 'delete';
                break;
            default:
                break;
        }
        $segments = $request->segments();
        $length = count($segments);
        $model = is_numeric($segments[$length - 1]) ? $segments[$length - 2] : $segments[$length - 1];
        $pathName = strtolower($model);
        $id = array_search($pathName, config('names.plural'));
        $className = 'App\\' . config('names.class.' . $id);
        $answer = ['error' => 'No permissions to do this'];
        var_dump($user, $gateName, $className);
        if ($user === null) {
                echo 'asdf';
            if ($gateName !== 'create' || $className !== 'App\\User') {
                // var_dump($user, $gateName, $className, 'App\User');
                // return response($answer, 403);
            }
        } else if ($user->cannot($gateName, $className)) {
            return response($answer, 403);
        }
        return $next($request);
    }
}
