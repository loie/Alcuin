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

function success($next_q) {
    $succ = '<strong class="text-success">Success</strong></li>';
    $succ .= '<li>' . $next_q . '&hellip;';
    return $succ;
}

function error($error, $details) {
    echo '<strong class="text-danger">Failed</strong></li>';
    echo '</ul>';
    echo '<div class="alert alert-danger" role="alert"><strong>An error occured:</strong> ' . $error;
    if ($details != null) {
        echo '<hr /><strong>Error message:</strong>';
        echo '<pre>' . $details . '</pre>';
    }
    echo '</div>';
    die();
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
    echo success('Trying to parse configuration file <code>' . $config_file . '</code>');
    $configuration = null;
    try {
        $configuration = new Configuration($config_file);
        assert($configuration != null);
        echo success('Checking for database settings');
        $db_conf = $configuration->db;
        if ($db_conf == null) {
            error('The file <code>'.$config_file.'</code> has not defined a database configuration');
        } else {
            echo success('Checking database properties');
            $db_required_props = array('host', 'name', 'user', 'password');
            $missing_prop = get_missing_prop($db_conf, $db_required_props);
            if ($missing_prop == null) {
                echo success('Connecting to server');
                try {
                    $db = new PDO('mysql:host='.$db_conf->host.';charset=utf8', $db_conf->user, $db_conf->password);
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                    try {
                        echo success('Checking whether database "' . $db_conf->name . '" exists');
                        // Check if DB exists
                        // Check is shall be dropped
                        // use DB
                        // foreach model
                            // create table
                            // foreach prop
                                // check if column exists
                                // add column with name, datatype
                                // add Index
                                
                        $db->exec('USE ' . $db_conf->name);
                    }
                    catch (Exception $e) {
                        error('The database ' . $db_conf->name . 'could not be connected', $e->getMessage());
                    }
                }
                catch (Exception $e) {
                   error('The server could not be connected', $e->getMessage());
                }
            } else {
                error('The file <code>'.$config_file.'</code> has not defined the property <code>'.$missing_prop.'</code> in the database configuration');
            }
        }
    }
    catch (Exception $e) {
        error('The file <code>'.$config_file.'</code> is not in valid JSON format');
    }
    
    // create php classes
    // foreach model
        // create php controller based on scaffold
        // create php model based on scaffold

} else {
    error('The file <code>'.$config_file.'</code> does not exists. Have you forgotten to ');
}

echo '</body>';
?>
