<?php

require 'vendor/autoload.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app = new \Slim\App;

$app->get('/hello', function (Request $request, Response $response) {
    $response->getBody()->write('LOL');
    return $response;
});
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->get('/salt', function (Request $request, Response $response) {
    $salt = [1,3,5,7,9,10,13,25,26,27,28,32];
    $time = microtime();
    $response->getBody()->write(hash('sha256', $time));
    return $response;
});
$app->run();
// require_once 'vendor/idiorm/idiorm.php';

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
// $app = new Slim\App();

// ORM::configure(array(
//     'connection_string' => 'mysql:host=localhost;dbname=test',
//     'username' => 'root',
//     'password' => 'bernie'
// ));

// ORM::configure('error_mode', PDO::ERRMODE_WARNING);
// ORM::configure('id_column', 'id');
// ORM::configure('id_column_overrides', array(
//     '$__history__users_roles' => '_revision_id',
//     'users_roles' => array('role_id', 'user_id')
// ));

// ORM::configure('caching_auto_clear', true);

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