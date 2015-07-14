<?php
include 'Request.php';

// autoload Models, Views and Controllers
spl_autoload_register('apiAutoload');
function apiAutoload ($classname) {
    if (preg_match('/[a-zA-Z]+Controller$/', $classname)) {
        include __DIR__ . '/controllers/' . $classname . '.php';
        return true;
    } elseif (preg_match('/[a-zA-Z]+Model$/', $classname)) {
        include __DIR__ . '/models/' . $classname . '.php';
        return true;
    } elseif (preg_match('/[a-zA-Z]+View$/', $classname)) {
        include __DIR__ . '/views/' . $classname . '.php';
        return true;
    }
}

$request = new Request($_SERVER, ("php://input"));

// route the request to the right place
$controller_name = ucfirst($request->url_elements[1] . 'Controller');
if (class_exists($controller_name)) {
    $controller = new $controller_name();

    echo $action_name;
    $action_name = strtolower($request->getVerb()) . '_action';
    $result = $controller->$action_name($request);
}
?>
