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

function getRouteConfig ($methodName, $controllerName) {
    return [
        'middleware' => ['oldage'],
        'uses' => $controllerName . 'Controller@' . $methodName
    ];
}

$app->post('/users', getRouteConfig('post', $controllerNames[USERS]));

foreach ($resources as $resource) {
    $controllerName = $controllerNames[$resource];
    $app->group([
        // 'middleware' => 'auth',
        'prefix' => $resource], function () use ($app, $controllerName) {
            if ($controllerName !== 'User') {
                $app->post('', getRouteConfig('post', $controllerName));
            }

            $app->get('', getRouteConfig('get', $controllerName));
            $app->get(ID, getRouteConfig('getId', $controllerName));

            $app->put(ID, getRouteConfig('putId', $controllerName));

            $app->delete('', getRouteConfig('delete', $controllerName));
            $app->delete(ID, getRouteConfig('deleteId', $controllerName));

            $app->patch(ID, getRouteConfig('patchId', $controllerName));

            $app->options('', getRouteConfig('options', $controllerName));
            $app->options(ID, getRouteConfig('optionsId', $controllerName));
    });
}

?>