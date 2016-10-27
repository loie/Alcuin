<?php
require 'alcuin/libs/Spyc.php';
require 'output.php';
require 'utils.php';
require 'db.php';
require 'lumen_install.php';
require 'lumen_rest.php';

function alcuin ($file) {
    // try to parse this file and use it as configuration object
    echo '<li>Searching for configuration file <code>' . $file . '</code>&hellip; ';
    $configuration = null;
    if (file_exists($file)) {
        assert(file_exists($file));
        success('Trying to parse configuration file <code>' . $file . '</code>');
        try {
            // create configuration from file using spyc
            $configuration = json_decode(json_encode(spyc_load_file($file)));
            if ($configuration === null) {
                throw new Exception($file . 'is not a valid YAML file');
            }
            assert(get_class($configuration) === 'stdClass');
            assert($configuration !== null);
        }
        catch (Exception $e) {
            error('The file <code>'.$this->file.'</code> is not in valid YAML format');
        }
    } else {
        error('The file <code>'.$file.'</code> does not exists. Have you forgotten to create it?');
    }

    success('Checking for database settings');
    if ($configuration->db === null) {
        error('The file <code>'.$file.'</code> has not defined a database configuration');
    } else {
        success('Checking database properties');
        $missing_prop = get_missing_prop($configuration->db, ['host', 'name', 'user', 'password']);
        if ($missing_prop == null) {
            success('Connecting to database server');
            try {
                // first, test the database connection
                $connection = connect_db($configuration);
            } catch (Exception $e) {
                error('The server could not be connected', $e->getMessage());
            }

            // Drop old database
            if ($configuration->drop_old_db) {
                success('Dropping old database because of configuration');
                try {
                    $connection->exec('DROP SCHEMA IF EXISTS `' . $configuration->db->name . '`');
                }
                catch (Exception $e) {
                    error('Could not drop old database ' . $configuration->db->name, $e->getMessage());
                }
            }

            // create new database
            success('Checking whether database <code>' . $configuration->db->name . '</code> exists and creating a new one if it doesn\'t exists');
            try {
                $connection->exec('CREATE SCHEMA IF NOT EXISTS`' . $configuration->db->name . '` DEFAULT CHARACTER SET utf8 ;');
            }
            catch (Exception $e) {
                error($e, 'Could not create new database');
            }
            success();
            // success(null);
            close_sub(); // Models close

            $processings = [
                array(
                    'description' => 'Creating model tables in database',
                    'func' => 'create_model_in_db'
                ),
                array(
                    'description' => 'Creating associative tables',
                    'func' => 'create_assoc_tables'
                ),
                array(
                    'description' => 'Creating indexes for all tables',
                    'func' => 'create_table_indexes'
                ),
                array(
                    'description' => 'Creating history tables',
                    'func' => 'create_history_tables'
                ),
                array(
                    'description' => 'Creating foreign keys for all tables',
                    'func' => 'create_foreign_keys'
                ),
                array(
                    'description' => 'Creating instances',
                    'func' => 'create_instances'
                ),
                // array(
                //     'description' => 'Installing Lumen',
                //     'func' => 'install_lumen'
                // ),
                array(
                    'description' => 'Creating Lumen Configuration Files',
                    'func' => 'create_lumen_config'
                ),
                array(
                    'description' => 'Creating Lumen Middleware',
                    'func' => 'create_lumen_middleware'
                ),
                array(
                    'description' => 'Creating Lumen Models',
                    'func' => 'create_lumen_models'
                ),
                array(
                    'description' => 'Creating Lumen Controllers',
                    'func' => 'create_lumen_controllers'
                ),
                array(
                    'description' => 'Creating Lumen Policies',
                    'func' => 'create_lumen_policies'
                ),
                array(
                    'description' => 'Creating Lumen Observers',
                    'func' => 'create_lumen_observers'
                ),
            ];
            foreach ($processings as $process) {
                open_sub($process['description']);
                call_user_func($process['func'], $configuration, $connection);
                close_sub();
            }
            try {
                // assert(true);
            } catch (Exception $e) {
                // error('The database ' . $configuration->db->name . ' could not be connected', $e->getMessage());
            }
        } else {
            error('Required properties are missing', $missing_prop);
        }
    }
}
?>