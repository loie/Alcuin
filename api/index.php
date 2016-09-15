<?php

require 'vendor/autoload.php';
require 'utils/utils.php';
require 'utils/constants.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
// autoload Models, Views and Controllers
spl_autoload_register('apiAutoload');
function apiAutoload ($classname) {
    foreach (Helpers::getDirMapping() as $class_regex => $dir_name) {
        if (preg_match($class_regex, $classname) &&
            file_exists(__DIR__ . $dir_name . $classname . '.php')) {
            require __DIR__ . $dir_name . $classname . '.php';
            return true;
        }
    }
    return false;
}

/* set up */
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db']['hostname'] = 'localhost';
$config['db']['db_name'] = 'test';
$config['db']['user'] = 'root';
$config['db']['password'] = 'bernie';

$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['db_name'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJECT);
    return $pdo;
};

$app->get('/hello', function (Request $request, Response $response) {
    $response->getBody()->write('LOL');
    return $response;
});
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->post('/users', function (Request $request, Response $response, $args) {
    $tier = $request->getParsedBody();
    // get permissions
    $allowed_actions = User::PERMISSIONS['entity'];
    $is_allowed = false;
    // is creation allowed in general?
    if (array_key_exists(PERMISSION_CREATE, $allowed_actions) {
        $allowed_roles = $allowed_actions[PERMISSION_CREATE];
        // is creation
        if (array_key_exists(PERMISSION_NONE, $allowed_roles)) {
            // no, is not allowed
        } else if (array_key_exists(PEMISSION_ALL, $allowed_roles)) {
            $is_allowed = true;
        } else {
            // get current user by fiddling with the user auth stuff
            $is_allowed = true;
        }
    }
    if ($is_allowed) {

    }
    // create model

    $response->getBody()->write(var_dump($tier));
    return $response;
});

$app->get('/users', function (Request $request, Response $response, $args) {
    return $reponse;
});

$app->get('/users/{id}', function (Request $request, Response $response, $args) {
    return $reponse;
});

$app->put('/users/{id}', function (Request $request, Response $response, $args) {
    return $reponse;
});

$app->patch('/users/{id}', function (Request $request, Response $response, $args) {
    return $reponse;
});

$app->delete('/users/{id}', function (Request $request, Response $response, $args) {
    return $reponse;
});

$app->get('/token', function (Request $request, Response $response) {
    $auth = $request->getParsedBody();

    if (isset($auth->email) && isset($auth->password_hash) && isset($auth->datetime)) {
        new User($container['db'], $auth);
    }

    $time = microtime();
    $length = strlen($time);
    foreach (SALT as $crumb) {
        $time .= $length > $crumb ? substr($time, $crumb, 1) : '';
    }

    $response->getBody()->write($time . ': ' . hash('sha256', $time));
    return $response;
});
$app->run();

// ORM::configure(array(
//     'connection_string' => 'mysql:host=' . $container['db']['hostname'] . ';dbname=' . $container['db']['db_name'],
//     'username' => $container['db']['user'],
//     'password' => $container['db']['password']
// ));

// ORM::configure('error_mode', PDO::ERRMODE_WARNING);
// ORM::configure('id_column', 'id');
// ORM::configure('id_column_overrides', array(
//     '$__history__answers' => '_revision_id',
//     '$__history__questions' => '_revision_id',
//     '$__history__questions_answers' => '_revision_id',
//     '$__history__questions_tags' => '_revision_id',
//     '$__history__roles' => '_revision_id',
//     '$__history__tags' => '_revision_id',
//     '$__history__users' => '_revision_id',
//     '$__history__users_roles' => '_revision_id',
//     'questions_answers' => array('answer_id', 'question_id')
//     'questions_tags' => array('tag_id', 'question_id')
//     'users_roles' => array('role_id', 'user_id')
// ));

// ORM::configure('caching_auto_clear', true);

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
// $app = new Slim\App();



// $lorenz = ORM::for_table('users')->where('email', 'lorenz.merdian@googlemail.com')->find_one();
// $users = ORM::for_table('users')->find_many();
// foreach ($users as $user) {
//     $user->token = microtime();
//     $user->save();
// }

/*

Map of all URIs:

User registration
/users         
/users/:id
/users/:id/questions
/users/:id/answers
/users/:id/roles

Login and Logoff
/sessions
/sessions/:id

Immer alle, eine ID und alle direkten Verbindungen
/questions
/questions/:id
/questions/:id/answers
/questions/:id/users
/questions/:id/tags

*/
?>