<?php
require      'vendor/autoload.php';
require_once 'vendor/idiorm/idiorm.php';

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new Slim\App();

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

// echo '<pre>';
// print_r($lorenz);
// echo '</pre>';
// echo '<pre>';
// print_r($one);
// echo '</pre>';
?>