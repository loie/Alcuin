<?php
include 'Request.php';

$request = new Request($_SERVER, file_get_contents("php://input"));

?>
