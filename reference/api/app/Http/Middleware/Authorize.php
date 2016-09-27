<?php

namespace App\Http\Middleware;

use Closure;
use App\Question as Question;
use App\Answer as Answer;
use App\Role as Role;
use App\User as User;
use App\Tag as Tag;
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

    protected static function MODEL_CLASS_NAME ($request) {
        $segments = $request->segments();
        $length = count($segments);
        $model = is_numeric($segments[$length - 1]) ? $segments[$length - 2] : $segments[$length - 1];
        $modelName = strtolower($model);
        $modelName = isset(self::$PLURALS[$modelName]) ? self::$PLURALS[$modelName] : substr($modelName, 0, strlen($modelName) - 1);
        return $modelName;
    }


   protected static function GATENAME ($request) {
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
        $className = 'App\\' . ucfirst(self::MODEL_CLASS_NAME($request));
        if ($user->cannot($gateName, $className)) {
            $answer = ['error' => 'No permissions to do this'];
            return response($answer, 403);
        }

        return $next($request);
    }
}
