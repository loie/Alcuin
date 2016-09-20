<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $app->get('/', function () use ($app) {
//     return $app->version();
// });


// $app->get('/foo', function () {
//     return 'Hello World';
// });

const ID = '/{id}';
const USERS = '/users';
const TAGS = '/tags';
const QUESTIONS = '/questions';
const ANSWERS = '/answers';

$resources = [USERS, TAGS, QUESTIONS, ANSWERS];
$controllerNames = [
    USERS => 'User',
    TAGS => 'Tag',
    QUESTIONS => 'Question',
    ANSWERS => 'Answer'
];

function getRouteConfig ($methodName, $controllerName, $middleware = null) {
    $injection = [];
    $injection['uses'] = $controllerName . 'Controller@' . $methodName;
    if (is_array($middleware) or is_string($middleware)) {
        $injection['middleware'] = $middleware;
    }
    return $injection;
}

$app->post('/tokens', getRouteConfig('create', 'Token'));
$app->post('/users', getRouteConfig('create', $controllerNames[USERS]));

foreach ($resources as $resource) {
    $controllerName = $controllerNames[$resource];
    $app->group([
        // 'middleware' => 'auth',
        'prefix' => $resource], function () use ($app, $controllerName) {
            $middleware = ['auth'];
            if ($controllerName !== 'User') {
                $app->post('', getRouteConfig('create', $controllerName, $middleware));
            }

            $app->get('', getRouteConfig('all', $controllerName, $middleware));
            $app->get(ID, getRouteConfig('read', $controllerName, $middleware));

            $app->put(ID, getRouteConfig('update', $controllerName, $middleware));
            $app->delete(ID, getRouteConfig('delete', $controllerName, $middleware));
            $app->patch(ID, getRouteConfig('patch', $controllerName, $middleware));


            $app->options('', getRouteConfig('optionsAll', $controllerName, $middleware));
            $app->options(ID, getRouteConfig('options', $controllerName, $middleware));
    });
}

?>