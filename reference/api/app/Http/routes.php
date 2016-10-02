<?php

const ID = '{id}';

function getRouteConfig ($methodName, $name, $middleware = null) {
    $injection = [];
    $injection['uses'] =  $name . 'Controller@' . $methodName;
    if (is_array($middleware) or is_string($middleware)) {
        $injection['middleware'] = $middleware;
    }
    return $injection;
}
$app->post('/tokens', getRouteConfig('create', 'Token'));
$app->post('/users', getRouteConfig('create', config('names.class.user')));


foreach (config('names.path') as $id => $path) {
    // $name = conf('names.path');
    // $names[$resource];
    $className = config('names.class.' . $id);

    $middleware = $className === 'User' ? ['can'] : ['auth', 'can'];
    $app->post($path, getRouteConfig('create', $className, $middleware));
    $app->get($path . '/' . ID, getRouteConfig('view', $className, $middleware));

            // $app->get('', getRouteConfig('all', $controllerName, $middleware));

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