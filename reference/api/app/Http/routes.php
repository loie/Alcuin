<?php

const ID = '{id}';
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
    $injection['uses'] =  $controllerName . 'Controller@' . $methodName;
    if (is_array($middleware) or is_string($middleware)) {
        $injection['middleware'] = $middleware;
    }
    print_r($injection);
    return $injection;
}

$app->post('/tokens', getRouteConfig('create', 'Token'));
$app->post('/users', getRouteConfig('create', $controllerNames[USERS]));

foreach ($resources as $resource) {
    $controllerName = $controllerNames[$resource];
    // $app->group(,
        // function () use ($app, $controllerName) {
            // $middleware = ['auth'];
            $middleware = null;
            if ($controllerName !== 'User') {
                $app->post($resource, getRouteConfig('create', $controllerName, $middleware));
            }

            // $app->get('', getRouteConfig('all', $controllerName, $middleware));
            // $app->get(ID, getRouteConfig('read', $controllerName, $middleware));

            // $app->put(ID, getRouteConfig('update', $controllerName, $middleware));
            // $app->delete(ID, getRouteConfig('delete', $controllerName, $middleware));
            // $app->patch(ID, getRouteConfig('patch', $controllerName, $middleware));


            // $app->options('', getRouteConfig('optionsAll', $controllerName, $middleware));
            // $app->options(ID, getRouteConfig('options', $controllerName, $middleware));
        // }
    // );
}

print_r($app);

?>