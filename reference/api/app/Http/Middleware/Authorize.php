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
            case 'PATCH':
                $gateName = 'patch';
                break;
            case 'DELETE':
                $gateName = 'delete';
                break;
            default:
                break;
        }
        $segments = $request->segments();
        $length = count($segments);
        $id = null;
        if (is_numeric($segments[$length - 1])) {
            $id = $segments[$length - 1];
            $model = $segments[$length - 2];
        } else {
            $model = $segments[$length - 1];
        }
        $pathName = strtolower($model);
        $stem = array_search($pathName, config('names.plural'));
        $className = 'App\\' . config('names.class.' . $stem);

        $answer = ['error' => 'No permissions to do this'];
        if ($user === null) {
            if ($gateName !== 'create' || $className !== 'App\\User') {
                return response($answer, 403);
            }
        } else {
            $model = $className::find($id);
            if ($model === null) {
                if (isset($id)) {
                    return response([
                        'error' => 'Not found'
                    ], 404);
                } else if ($user->cannot($gateName, $className)) {
                    return response($answer, 403);
                }
            } else if ($user->cannot($gateName, $model)) {
                return response($answer, 403);
            }
        }
        return $next($request);
    }
}
