<?php
include 'Configuration.php';

$config_file = 'configuration.json';

function get_missing_prop($obj, $prop_array) {
    foreach ($prop_array as $prop) {
        if ($obj->{$prop} == null) {
            return $prop;
        }
    }
    return null;
}

echo '<!DOCTYPE html>
    <head>
        <title>Alcuin &mdash; DB and REST for the layy ones</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
    </head>
    <body><div class="container"><h1>Alcuin</h1><hr /><ul class="list-unstyled">';

// check for existing of configuration.json
echo '<li>Searching for configuration file <code>' . $config_file . '</code>&hellip; ';
if (file_exists($config_file)) {
    echo '<strong class="text-success">Success</strong></li>';
    echo '<li>Trying to parse configuration file <code>' . $config_file . '</code>&hellip; ';
    $configuration = null;
    try {
        $configuration = new Configuration($config_file);
        assert($configuration != null);
        echo '<strong class="text-success">Success</strong></li>';
        echo '<li>Checking for database settings &hellip; ';
        $db_conf = $configuration->db;
        if ($db_conf == null) {
            echo '<strong class="text-danger">Failed</strong></li>';
            echo '</ul>';
            echo '<div class="alert alert-danger" role="alert"><strong>An error occured:</strong> The file <code>'.$config_file.'</code> has not defined a database configuration.</div>';
            die();
        } else {
            echo '<strong class="text-success">Success</strong></li>';
            echo '<li>Checking database properties &hellip; ';
            $db_required_props = array('host', 'name', 'user', 'password');
            $missing_prop = get_missing_prop($db_conf, $db_required_props);
            if ($missing_prop == null) {
                $db = new PDO('mysql:host='.$db_conf->host.';charset=utf8', $db_conf->user, $db_conf->password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $db->exec('USE ' . $db->name);
            } else {
                echo '<strong class="text-danger">Failed</strong></li>';
                echo '</ul>';
                echo '<div class="alert alert-danger" role="alert"><strong>An error occured:</strong> The file <code>'.$config_file.'</code> has not defined the property <code>'.$missing_prop.'</code> in the database configuration.</div>';
                die();
            }
        }
    }
    catch (Exception $e) {
        echo '<strong class="text-danger">Failed</strong></li>';
        echo '</ul>';
        echo '<div class="alert alert-danger" role="alert"><strong>An error occured:</strong> The file <code>'.$config_file.'</code> is not in valid JSON format.</div>';
        die();
    }

} else {
    echo '<strong class="text-danger">Failed</strong></li>';
    echo '</ul>';
    echo '<div class="alert alert-danger" role="alert"><strong>An error occured:</strong> The file <code>'.$config_file.'</code> does not exists.</div>';
}

echo '</body>';

// check for correct json format of configuration.json
// create "ready to go" button
// perform db install
// perform php class generation


?>
