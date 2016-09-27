<?php

const ID = '{id}';
const USERS = '/users';
const TAGS = '/tags';
const QUESTIONS = '/questions';
const ANSWERS = '/answers';

$resources = [USERS, TAGS, QUESTIONS, ANSWERS];
$names = [
    USERS => 'User',
    TAGS => 'Tag',
    QUESTIONS => 'Question',
    ANSWERS => 'Answer'
];

function getRouteConfig ($methodName, $name, $middleware = null) {
    $injection = [];
    $injection['uses'] =  $name . 'Controller@' . $methodName;
    if (is_array($middleware) or is_string($middleware)) {
        $injection['middleware'] = $middleware;
    }
    return $injection;
}
$app->post('/tokens', getRouteConfig('create', 'Token'));
$app->post('/users', getRouteConfig('create', $names[USERS]));


foreach (config('names.path') as $id => $path) {
    // $name = conf('names.path');
    // $names[$resource];
    $className = config('names.class.' . $id);

    $middleware = ['auth', 'can'];
    if ($className !== 'User') {
        $app->post($path, getRouteConfig('create', $className, $middleware));
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

// print_r($app);

?>