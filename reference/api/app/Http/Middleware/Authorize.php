<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authorize
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;


    protected static $PLURALS = [];

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


   protected static function GATENAME ($request) {
        $gateName = null;
        switch ($request->method()) {
            case 'GET':
                $gateName = 'read';
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
        $modelName = strtolower($model);
        $modelName = isset(self::$PLURALS[$modelName]) ? : substr($modelName, 0, strlen($modelName) - 1);
        $gateName .= '-' . $modelName;
        return $gateName;
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
        $gateName = self::GATENAME($request);
        var_dump($user->can($gateName));
        if ($user->cant($gateName)) {
            abort(401);
        }

        return $next($request);
    }
}
