<?php
include 'includes/Configuration.php';
include 'includes/Property.php';
include 'includes/Main.php';

$config_file = 'configuration.json';

echo '<!DOCTYPE html>
            <head>
                <title>Alcuin &mdash; DB and REST for the layy ones</title>
                <link rel="stylesheet" href="css/bootstrap.min.css">
            </head>
            <body><div class="container"><h1>Alcuin</h1><hr /><ul class="list-unstyled">';


$main = new Main($config_file);
$main->exec();
?>
