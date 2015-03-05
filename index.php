<?php
include 'Configuration.php';

$config_file = 'configuration.json';

function has_required_props§($obj, $prop_array) {
    foreach ($prop_array as $prop) {
        if ($obj->{$prop_array} == null) {
            return false;
        }
    }
    return true;
}

echo '<!DOCTYPE html>
    <head>
        <title>Alcuin &mdash; DB and REST for the layy ones</title>
    </head>
    <body>';

// check for existing of configuration.json
if (file_exists($config_file)) {
    $configuration = null;
    try {
        $confguration = new Configuration($config_file);            
    }
    catch (Exception $e) {
        echo '<h3 class="text-danger">'.$config_file .' is not in valid JSON format</h3>';
        echo '<pre>'.$e.'</pre>';
    }
    if ($configuration != null) {
        $db_conf = $configuration->db;
        if ($db_conf == null) {
            echo '<h3 class="text-danger">'.$config_file .' has not defined database configuration</h3>';
        } else {
            $db_required_props = array('host', 'name', 'user', 'password');
            if (has_required_props§($db_conf, $db_required_props)) {
                $db = new PDO('mysql:host='.$db_conf->host.';charset=utf8', $db_conf->user, $db_conf->password);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $db->exec();
            } else {
                echo '<h3 class="text-danger">'.$config_file .' has not defined databse properties</h3>';
            }
        }
    }

} else {
    echo '<h3 class="text-danger">'.$config_file.' does not exists.</h3>';
}

echo '</body>';

// check for correct json format of configuration.json
// create "ready to go" button
// perform db install
// perform php class generation


?>
