s<?php
    const BELONGS_TO = 'belongs_to';
    const HAS_MANY = 'has_many';
    const BELONGS_TO_AND_HAS_MANY = 'belongs_to_and_has_many';
    const HISTORY_TABLE_PREFIX = '$__history__';

    function create_model_in_db ($configuration, $connection) {
        $models = $configuration->architecture->models;
        assert($models !== null);
        foreach ($models as $model_name => $model) {
            next_item('Creating table for Model <code>' . $model_name . '</code>');        
            $model_name_table = get_model_plural_name($model_name, $model);

            $query_string = 'CREATE TABLE `' . $configuration->db->name . '`.`' . $model_name_table . '` (';
            $statements = [];
            // id column
            array_push($statements, "`id` INT NOT null AUTO_INCREMENT COMMENT 'Primary Key for this table' ");

            // Insert relationship columns first
            $columns = get_columns($model);
            foreach ($columns as $column) {
                array_push($statements, $column->statement);
            }

            // auth token column for models used for authentification
            if ($configuration->architecture->use_for_auth === $model_name) {
                $line = "`token` CHAR(64) CHARACTER SET 'utf8'";
                array_push($statements, $line);
                $line = "`expires` TIMESTAMP NULL";
                array_push($statements, $line);
            }

            // set primary key
            array_push($statements, 'PRIMARY KEY (`id`)');
            $query_string .= implode(', ', $statements);
            $query_string .= ');';
            try {
                $connection->exec($query_string);
            } catch (Exception $e) {
                error('Could not create table for ' . $model_name. '.', $e);
            }
            success();
        }
    }

    function create_assoc_tables ($configuration, $connection) {
        foreach ($configuration->architecture->models as $model_name => $model_properties) {
            if (isset($model_properties->relations)) {
                // this model is related to other models
                foreach ($model_properties->relations as $relation_name => $relation_properties) {
                    if ($relation_properties->type === BELONGS_TO_AND_HAS_MANY) {
                        // yeah, create some associative table here
                        if (isset($relation_properties->via_table)) {
                            $query_string = 'CREATE TABLE IF NOT EXISTS `' . $configuration->db->name . '`.`' . $relation_properties->via_table . '` (';
                            $statements = [];
                            // id column
                            array_push($statements, '`' . $model_name . '_id` INT NOT null');
                            array_push($statements, '`' . $relation_properties->model . '_id` INT NOT null');
                            $query_string .= implode(', ', $statements);
                            $query_string .= ');';
                            try {
                                next_item('Creating associative table if not exists <code>' . $relation_properties->via_table . '</code> for relation ' . $model_name . ' <-> ' . $relation_properties->model);
                                $connection->exec($query_string);
                                success();
                            } catch (Exception $e) {
                                error('Could not create accociative table ' . $relation_properties->via_table. '.', $e->getMessage());
                            }

                        } else {
                            error ('No associative table defined for relation ' . $model_name . ' <-> ' . $model_properties->model);
                        }
                    }
                }
            }
        }
    }

    function create_table_indexes ($configuration, $connection) {
        foreach ($configuration->architecture->models as $model_name => $model_properties) {
            // go through all model relations
            if (isset($model_properties->relations)) {
                foreach ($model_properties->relations as $relation_name => $relation_properties) {
                    $query = null;
                    $skip = false;
                    $has_relation = false;
                    if (isset($relation_properties->type)) {
                        $model_table_name = get_model_plural_name($model_name, $model_properties);
                        switch ($relation_properties->type) {
                            case BELONGS_TO:
                                $has_relation = true;
                                $prepared_query = 'ALTER TABLE `{{db_name}}`.`{{table_name}}` ADD INDEX `{{relation_name}}_INDEX` (`{{relation_name}}_id` ASC);';
                                $query = fill_with_data($prepared_query, array(
                                        'db_name' => $configuration->db->name,
                                        'table_name' => $model_table_name,
                                        'relation_name' => $relation_name
                                    ));
                                break;
                            case BELONGS_TO_AND_HAS_MANY:
                                $has_relation = true;
                                $index_name = $relation_properties->via_table . '_UNIQUE';
                                $prepared_index_query = 'SHOW INDEX FROM `{{db_name}}`.`{{table_name}}` WHERE Key_name = \'{{key_name}}\'';
                                $index_query = fill_with_data($prepared_index_query, array(
                                        'db_name' => $configuration->db->name,
                                        'table_name' => $relation_properties->via_table,
                                        'key_name' => $index_name
                                    ));
                                $result = $connection->query($index_query);
                                if ($result->rowCount() == 0) {
                                    $prepared_query = 'ALTER TABLE `{{db_name}}`.`{{table_name}}` ADD UNIQUE INDEX `{{index_name}}` (`{{this_model_name}}_id` ASC, `{{other_model_name}}_id` ASC);';
                                    $query = fill_with_data($prepared_query, array(
                                            'db_name' => $configuration->db->name,
                                            'table_name' => $relation_properties->via_table,
                                            'index_name' => $index_name,
                                            'this_model_name' => $model_name,
                                            'other_model_name' => $relation_properties->model
                                        ));
                                } else {
                                    // no need to create index, since it already exists
                                    $skip = true;
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    if ($has_relation) {
                        next_item('Creating index for relation <code>' . $model_table_name . '</code> and <code>' . $relation_name . '</code>');
                        if ($skip) {
                            skipped();
                        } else {
                            try {
                                $connection->exec($query);
                                success();
                            } catch (Exception $e) {
                                error('Could not create index for relation <code>' . $model_table_name . '</code> and <code>' . $relation_name . '</code>', $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }

    function create_history_tables ($configuration, $connection) {
        $prepared_query = 'CREATE TABLE `{{db_name}}`.`' . HISTORY_TABLE_PREFIX .'{{table_name}}` LIKE `{{db_name}}`.`{{table_name}}`;';
        $prepared_alter_query = 'ALTER TABLE `{{db_name}}`.`' . HISTORY_TABLE_PREFIX .'{{table_name}}`
            CHANGE `id` `id` int(10) UNSIGNED,
            DROP PRIMARY KEY,
            ADD `_revision` BIGINT UNSIGNED AUTO_INCREMENT,
            ADD `_revision_previous` BIGINT UNSIGNED NULL,
            ADD `_revision_timestamp` DATETIME NULL DEFAULT NULL,
            ADD `_revision_is_terminal` INT(1) NOT NULL DEFAULT 0,
            ADD PRIMARY KEY (`_revision`),
            ADD INDEX (`_revision_previous`),
            ADD INDEX `org_primary` (`id`);';
        $prepared_assoc_query = 'CREATE TABLE IF NOT EXISTS `{{db_name}}`.`' . HISTORY_TABLE_PREFIX .'{{assoc_table}}`
            (`{{model_name}}_id` INT NOT NULL,
            `{{relation_model_name}}_id` INT NOT NULL,
            `_revision` BIGINT UNSIGNED AUTO_INCREMENT,
            `_revision_previous` BIGINT UNSIGNED NULL,
            `_revision_timestamp` DATETIME NULL DEFAULT NULL,
            `_revision_is_terminal` INT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`_revision`),
            INDEX (`_revision_previous`));';
        $prepared_trigger_after_insert_query = '
            CREATE TRIGGER {{table_name}}_insert_trigger
            AFTER INSERT
                ON {{table_name}} FOR EACH ROW
            BEGIN
                INSERT INTO `' . HISTORY_TABLE_PREFIX . '{{table_name}}` (
                    `id`,
                    {{column_names}},
                    `_revision_previous`,
                    `_revision_timestamp`
                ) VALUES (
                    NEW.id,
                    {{new_column_names}},
                    NULL,
                    CURRENT_TIMESTAMP());
            END';
        $prepared_via_table_trigger_after_insert_query = '
            CREATE TRIGGER {{table_name}}_insert_trigger
            AFTER INSERT
                ON {{table_name}} FOR EACH ROW
            BEGIN
                INSERT INTO `' . HISTORY_TABLE_PREFIX . '{{table_name}}` (
                    {{this_model_name}}_id,
                    {{relation_model_name}}_id,
                    `_revision_previous`,
                    `_revision_timestamp`
                ) VALUES (
                    NEW.{{this_model_name}}_id,
                    NEW.{{relation_model_name}}_id,
                    NULL,
                    CURRENT_TIMESTAMP());
            END';
        $prepared_trigger_after_update_query = '
            CREATE TRIGGER {{table_name}}_update_trigger
                AFTER UPDATE
                    ON {{table_name}}
                FOR EACH ROW 
                BEGIN
                    DECLARE prevRevision INT UNSIGNED;
                    SELECT `_revision` FROM `' . HISTORY_TABLE_PREFIX . '{{table_name}}` WHERE `id` = NEW.id ORDER BY `_revision` DESC LIMIT 1 INTO prevRevision;
                    INSERT INTO `' . HISTORY_TABLE_PREFIX . '{{table_name}}` (
                        `id`,
                        {{column_names}},
                        `_revision_previous`,
                        `_revision_timestamp`
                    ) VALUES (
                        NEW.id,
                        {{new_column_names}},
                        prevRevision,
                        CURRENT_TIMESTAMP()
                    );
                END';
        $prepared_trigger_after_delete_query = '
            CREATE TRIGGER {{table_name}}_delete_trigger
                AFTER DELETE
                    ON {{table_name}}
                FOR EACH ROW 
                BEGIN
                    DECLARE prevRevision INT UNSIGNED;
                    SELECT `_revision` FROM `' . HISTORY_TABLE_PREFIX . '{{table_name}}` WHERE `id` = OLD.id ORDER BY `_revision` DESC LIMIT 1 INTO prevRevision;
                    INSERT INTO `' . HISTORY_TABLE_PREFIX . '{{table_name}}` (
                        `id`,
                        {{column_names}},
                        `_revision_previous`,
                        `_revision_timestamp`,
                        `_revision_is_terminal`
                    ) VALUES (
                        OLD.id,
                        {{old_column_names}},
                        prevRevision,
                        CURRENT_TIMESTAMP(),
                        1
                    );
                END';
        $prepared_via_table_trigger_after_delete_query = '
            CREATE TRIGGER {{table_name}}_delete_trigger
                AFTER DELETE
                    ON {{table_name}}
                FOR EACH ROW 
                BEGIN
                    DECLARE prevRevision INT UNSIGNED;
                    SELECT `_revision` FROM `' . HISTORY_TABLE_PREFIX . '{{table_name}}` WHERE `{{this_model_name}}_id` = OLD.{{this_model_name}}_id AND `{{relation_model_name}}_id` = OLD.{{relation_model_name}}_id ORDER BY `_revision` DESC LIMIT 1 INTO prevRevision;
                    INSERT INTO `' . HISTORY_TABLE_PREFIX . '{{table_name}}` (
                        `{{this_model_name}}_id`,
                        `{{relation_model_name}}_id`,
                        `_revision_previous`,
                        `_revision_timestamp`,
                        `_revision_is_terminal`
                    ) VALUES (
                        OLD.{{this_model_name}}_id,
                        OLD.{{relation_model_name}}_id,
                        prevRevision,
                        CURRENT_TIMESTAMP(),
                        1
                    );
                END';
        foreach ($configuration->architecture->models as $model_name => $model_properties) {
            try {
                // creating history table
                next_item('Creating history table for <code>' . $model_name . '</code>');
                $table_name = get_model_plural_name($model_name, $model_properties);
                $query = fill_with_data($prepared_query, array(
                        'db_name' => $configuration->db->name,
                        'table_name' => $table_name
                    ));
                $connection->exec($query);

                $alter_query = fill_with_data($prepared_alter_query, array(
                        'db_name' => $configuration->db->name,
                        'table_name' => $table_name
                    ));
                $connection->exec($alter_query);
                success();

                // creating trigger for history table
                next_item('Creating triggers for <code>' . $model_name . '</code>');
                $columns = get_columns($model_properties);
                $column_names = array_map(function ($column) {
                    return '`' . $column->name . '`';
                }, $columns);
                $new_column_names = array_map(function ($column) {
                    return 'NEW.'.$column->name;
                }, $columns);
                $old_column_names = array_map(function ($column) {
                    return 'OLD.'.$column->name;
                }, $columns);
                $triggers = [$prepared_trigger_after_insert_query,
                    $prepared_trigger_after_update_query,
                    $prepared_trigger_after_delete_query];
                foreach ($triggers as $prepared_trigger_query) {
                    $trigger_query = fill_with_data($prepared_trigger_query, array(
                        'db_name' => $configuration->db->name,
                        'table_name' => $table_name,
                        'column_names' => $column_names,
                        'new_column_names' => $new_column_names,
                        'old_column_names' => $old_column_names));
                    $connection->exec('USE ' . $configuration->db->name);
                    $connection->exec($trigger_query);
                }
                success();

                foreach ($model_properties->relations as $relation_name => $relation_properties) {
                    if ($relation_properties->type === BELONGS_TO_AND_HAS_MANY) {
                        next_item('Creating history table for associative table <code>' . $relation_properties->via_table . '</code>');
                        $assoc_query = fill_with_data($prepared_assoc_query, array(
                            'db_name' => $configuration->db->name,
                            'assoc_table' => $relation_properties->via_table,
                            'model_name' => $model_name,
                            'relation_model_name' => $relation_properties->model
                        ));
                        $connection->exec($assoc_query);
                        success();
                        next_item('Creating triggers for associative table <code>' . $relation_properties->via_table . '</code>');
                        $via_table_triggers = [$prepared_via_table_trigger_after_insert_query, $prepared_via_table_trigger_after_delete_query];
                        $prepared_check_trigger_query = "SELECT TRIGGER_NAME FROM information_schema.triggers WHERE TRIGGER_SCHEMA = '{{db_name}}' AND (TRIGGER_NAME = '{{table_name}}_delete_trigger' OR TRIGGER_NAME = '{{table_name}}_insert_trigger')";
                        $check_trigger_query = fill_with_data($prepared_check_trigger_query, array(
                            'db_name' => $configuration->db->name,
                            'table_name' => $relation_properties->via_table
                        ));
                        if ($connection->query($check_trigger_query)->rowCount() < count($via_table_triggers)) {
                            foreach ($via_table_triggers as $prepared_trigger_query) {
                                $connection->exec('USE ' . $configuration->db->name);
                                $trigger_query = fill_with_data($prepared_trigger_query, array(
                                    'db_name' => $configuration->db->name,
                                    'table_name' => $relation_properties->via_table,
                                    'this_model_name' => $model_name,
                                    'relation_model_name' => $relation_properties->model,
                                ));
                                $connection->exec($trigger_query);
                            }
                            success();
                        } else {
                            skipped();
                        }
                    }
                }
            }
            catch (Exception $e) {
                error('Error during creation of history tables', $e->getMessage());
            }

        }
    }

    function create_foreign_keys ($configuration, $connection) {
        /* We use RESTRICT because of the MySQL bug 
         * https://bugs.mysql.com/bug.php?id=11472
         * Deleting thing mean, the application layer has to take care of this
         */
        $prepared_query = 'ALTER TABLE `{{db_name}}`.`{{table_name}}` ADD CONSTRAINT `{{model_name}}_{{fk_model}}_FK` FOREIGN KEY (`{{fk_model}}_id`) REFERENCES `{{db_name}}`.`{{fk_table_name}}` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;';
        foreach ($configuration->architecture->models as $model_name => $model_properties) {
            try {
                if (isset($model_properties->relations)) {
                    foreach ($model_properties->relations as $relation_name => $relation_properties) {
                        $query = null;
                        $skip = false;
                        $has_relation = false;
                        if (isset($relation_properties->type)) {
                            $model_table_name = get_model_plural_name($model_name, $model_properties);
                            switch ($relation_properties->type) {
                                case BELONGS_TO:
                                    $query = fill_with_data($prepared_query, array(
                                        'db_name' => $configuration->db->name,
                                        'model_name' => $model_name,
                                        'table_name' => get_model_plural_name($model_name, $model_properties),
                                        'fk_model' => $relation_name,
                                        'fk_table_name' => get_model_plural_name($relation_properties->model, $configuration->architecture->models->{$relation_name})
                                    ));
                                    next_item('Creating foreign key constraints for <code>' . $model_name . '</code> and <code>' . $relation_properties->model . '</code>');
                                    $connection->exec($query);
                                    success();
                                    break;
                                case BELONGS_TO_AND_HAS_MANY:
                                    $query = fill_with_data($prepared_query, array(
                                        'db_name' => $configuration->db->name,
                                        'model_name' => $model_name,
                                        'table_name' => $relation_properties->via_table,
                                        'fk_model' => $relation_properties->model,
                                        'fk_table_name' => get_model_plural_name($relation_properties->model, $configuration->architecture->models->{$relation_properties->model})
                                    ));
                                    next_item('Creating foreign key constraints for <code>' . $model_name . '</code> and <code>' . $relation_properties->model . '</code>');
                                    $connection->exec($query);
                                    success();
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
            }
            catch (Exception $e) {
                error('Could not create containt', $e->getMessage());
            }
        }
    }

    function create_instances ($configuration, $connection) {
        $models = $configuration->architecture->models;
        assert($models !== null);
        foreach ($models as $model_name => $model_properties) {
            if (isset($model_properties->instances)) {
                assert($model_properties->instances !== null);
                if (is_array($model_properties->instances)) {
                    next_item('Creating instance for model <code>' . $model_name . '</code>');
                    $prepared_insert_query = 'INSERT INTO `{{db_name}}`.`{{table_name}}` ({{column_names}}) VALUES ({{values}});';
                    foreach ($model_properties->instances as $instance) {
                        $keys = get_object_vars($instance);
                        $column_names = [];
                        $values = [];
                        foreach ($keys as $key => $value) {
                            array_push($column_names, '`' . $key . '`');
                            array_push($values, '\'' . $value . '\'');
                        }
                        $insert_query = fill_with_data($prepared_insert_query, array(
                            'db_name' => $configuration->db->name,
                            'table_name' => get_model_plural_name($model_name, $model_properties),
                            'column_names' => $column_names,
                            'values' => $values
                        ));
                        $connection->exec($insert_query);
                    }
                    success();
                }
            }
        }
    }
        
    /* returns an active database connection */
    function connect_db ($configuration) {
        $connection = new PDO('mysql:host='.$configuration->db->host.';charset=utf8', $configuration->db->user, $configuration->db->password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $connection;
    }

    function get_db_column_statement ($property_name, $properties) {
        $line = '`' . $property_name . '` ';
        $default = NULL;

        if (is_array($properties->type)) {
            $line .= 'ENUM(';
            $items = [];
            foreach ($properties->type as $enum) {
                array_push($items, "'" . $enum . "'");
            }
            $line .= implode(',', $items);
            $line .= ')';
        }
        else {
            switch ($properties->type) {
                case 'string':
                    if (isset($properties->max_length) && is_numeric($properties->max_length)) {
                        $line .= 'VARCHAR(' . $properties->max_length . ") CHARACTER SET 'utf8'";
                    } else {
                        $line .= "TEXT CHARACTER SET 'utf8'";
                    }
                    $default = "'" . $properties->default . "'";
                    break;
                case 'hash':
                    $line .= "CHAR(64) CHARACTER SET 'utf8'";
                    break;
                case 'datetime':
                    $line .= 'DATETIME';
                    $default = "'0000-00-00 00:00:00'";
                    break;
                case 'int':
                    $line .= 'INT';
                    $default = $properties->default;
                    break;
                case 'float':
                    $line .= 'DOUBLE';
                    $default = $properties->default;
                    break;
                case 'bool':
                    $line .= "TINYINT(1)";
                    if ($properties->default == true) {
                        $default = '1';
                    } else {
                        $default = '0';
                    }
                    break;
                case 'email':
                    $line .= "VARCHAR(255) CHARACTER SET 'utf8'";
                    break;
                default:
                    error('Unknown column type ' . $properties->type);
                    break;
            }
        }
        $line .= $properties->null_allowed ? '' : ' NOT';
        $line .= ' NULL';
        if (isset($properties->default)) {
            $line .= ' DEFAULT ' . $default;
        }
        return $line;
    }

    function fill_with_data ($prepared_query, $replacements) {
        $query = $prepared_query;
        foreach ($replacements as $key => $value) {
            if (is_string($value)) {
                $query = str_replace('{{' . $key . '}}', $value, $query);
            } else if (is_array($value)) {
                $flat_value = implode(',', $value);
                $query = str_replace('{{' . $key . '}}', $flat_value, $query);
            }
        }
        return $query;
    }

    function get_columns ($model) {
        $columns = [];

        if (isset($model->relations)) {
            foreach ($model->relations as $relation_name => $relation) {
                if ($relation->type === BELONGS_TO) {
                    $column = new stdClass();
                    $column->name = $relation_name . '_id';
                    $column->statement = '`' . $column->name . '` INT NOT NULL';
                    array_push($columns, $column);
                }
            }
        }

        // other proerties columns
        if (isset($model->properties) && isset($model->properties->list)) {
            foreach ($model->properties->list as $column_name => $properties) {
                $column = new stdClass();
                $column->name = $column_name;
                $column->statement = get_db_column_statement($column_name, $properties);
                array_push($columns, $column);
            }
        }

        return $columns;
    }
?>