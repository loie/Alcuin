<?php
include_once 'DB.php';
include_once 'Request.php';
include_once 'ORM.php';
include_once 'utils/Access.php';
include_once 'utils/Permissions.php';
include_once 'utils/Helpers.php';
include_once 'utlils/HTTPStatusException.php';
include_once 'models/Model.php';
include_once 'controllers/Controller.php';

// autoload Models, Views and Controllers
spl_autoload_register('apiAutoload');
function apiAutoload ($classname) {
    foreach (Helpers::getDirMapping() as $class_regex => $dir_name) {
        if (preg_match($class_regex, $classname) &&
            file_exists(__DIR__ . $dir_name . $classname . '.php')) {
            include __DIR__ . $dir_name . $classname . '.php';
            return true;
        }
    }
    return false;
}
$request = new Request($_SERVER, file_get_contents("php://input"));

// route the request to the right place
$controller_name = ucfirst($request->getHandlerName() . 'Controller');
if (class_exists($controller_name)) {
    $controller = new $controller_name();
    $action_name = strtolower($request->getVerb()) . '_action';
    $result = $controller->$action_name($request);
}
?>
