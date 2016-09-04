<?php
    const BELONGS_TO = 'belongs_to';
    const HAS_MANY = 'has_many';
    const BELONGS_TO_AND_HAS_MANY = 'belongs_to_and_has_many';
    const HISTORY_TABLE_PREFIX = '$__history__';

    function create_model_in_db ($configuration, $connection) {
        $models = $configuration->architecture->models;
        assert($models !== null);
        foreach ($models as $model_name => $model) {
            next_item('Creating table for Model <code>' . $model_name . '</code>');        
            $model_name_table = get_model_table_name($model_name, $model);

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
                $line = "`token` CHAR(40) CHARACTER SET 'utf8'";
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
                        $model_table_name = get_model_table_name($model_name, $model_properties);
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
            ADD `_revision_user_id` INT UNSIGNED NULL,
            ADD `_revision_timestamp` DATETIME NULL DEFAULT NULL,
            ADD PRIMARY KEY (`_revision`),
            ADD INDEX (`_revision_previous`),
            ADD INDEX `org_primary` (`id`);';
        $prepared_assoc_query = 'CREATE TABLE IF NOT EXISTS `{{db_name}}`.`' . HISTORY_TABLE_PREFIX .'{{assoc_table}}`
            (`{{model_name}}_id` INT NOT NULL,
            `{{relation_model_name}}_id` INT NOT NULL,
            `_revision` BIGINT UNSIGNED AUTO_INCREMENT,
            `_revision_previous` BIGINT UNSIGNED NULL,
            `_revision_user_id` INT UNSIGNED NULL,
            `_revision_timestamp` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`_revision`),
            INDEX (`_revision_previous`));';
        $prepared_trigger_after_insert_query = 'DELIMITER //
CREATE TRIGGER `{{table_name}}_insert_trigger`
AFTER INSERT
    ON `{{db_name}}`.`{{table_name}}` FOR EACH ROW
BEGIN
    INSERT INTO `' . HISTORY_TABLE_PREFIX . '{{table_name}}` (
        {{column_names}},
        `_revision`,
        `_revision_previous`,
        `_revision_user_id`,
        `_revision_timestamp`
    ) VALUES (
        {{new_column_names}}
        0,
        NULL,
        0,
        CURRENT_TIMESTAMP());
END; //
DELIMITER ;';
        $prepared_after_update_query = '
            DELIMITER //
            CREATE TRIGGER `questions_answers_insert_trigger`
            AFTER INSERT
                ON `questions_answers` FOR EACH ROW
            BEGIN
                DECLARE `prevRevision` INT(10) UNSIGNED;
                INSERT INTO `$__history__answers` (
                    question_id,
                    answer_id,
                    _revision,
                    _revision_previous,
                    _revision_user_id,
                    _revision_timestamp
                ) VALUES (
                    NEW.question_id,
                    NEW.answer_id,
                    0,
                    null,
                    0,
                    current_timestamp());
            END; //
            DELIMITER ;';
        $prepared_after_delete_query = '';
        foreach ($configuration->architecture->models as $model_name => $model_properties) {
            try {
                // creating history table
                next_item('Creating history table for <code>' . $model_name . '</code>');
                $table_name = get_model_table_name($model_name, $model_properties);
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
                next_item('Creating history table for <code>' . $model_name . '</code>');
                $columns = get_columns($model_properties);
                $column_names = array_map(function ($column) {
                    return '`' . $column->name . '`';
                }, $columns);
                $new_column_names = array_map(function ($column) {
                    return 'NEW.'.$column->name;
                }, $columns);
                $trigger_after_insert_query = fill_with_data($prepared_trigger_after_insert_query, array(
                        'db_name' => $configuration->db->name,
                        'table_name' => $table_name,
                        'column_names' => $column_names,
                        'new_column_names' => $new_column_names
                    ));
                echo '<pre>' . $trigger_after_insert_query . '</pre>';
                $connection->exec($trigger_after_insert_query);
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
                        next_item('Creating after insert trigger for associative table <code>' . $relation_properties->via_table . '</code>');
                        success();
                    }
                }
            }
            catch (Exception $e) {
                error('Error during creation of history tables', $e->getMessage());
            }

        }
    }

    function create_foreign_keys ($configuration, $connection) {
        $prepared_query = 'ALTER TABLE `{{db_name}}`.`{{table_name}}` ADD CONSTRAINT `{{model_name}}_{{fk_model}}_FK` FOREIGN KEY (`{{fk_model}}_id`) REFERENCES `{{db_name}}`.`{{fk_table_name}}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;';
        foreach ($configuration->architecture->models as $model_name => $model_properties) {
            try {
                if (isset($model_properties->relations)) {
                    foreach ($model_properties->relations as $relation_name => $relation_properties) {
                        $query = null;
                        $skip = false;
                        $has_relation = false;
                        if (isset($relation_properties->type)) {
                            $model_table_name = get_model_table_name($model_name, $model_properties);
                            switch ($relation_properties->type) {
                                case BELONGS_TO:
                                    $query = fill_with_data($prepared_query, array(
                                        'db_name' => $configuration->db->name,
                                        'model_name' => $model_name,
                                        'table_name' => get_model_table_name($model_name, $model_properties),
                                        'fk_model' => $relation_name,
                                        'fk_table_name' => get_model_table_name($relation_properties->model, $configuration->architecture->models->{$relation_name})
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
                                        'fk_table_name' => get_model_table_name($relation_properties->model, $configuration->architecture->models->{$relation_properties->model})
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

    /* Get table name for model */
    function get_model_table_name ($model_name, $model_properties) {
        $model_table_name = null;
        if (is_object($model_properties)) {
            $model_table_name = $model->name_plural;
        }
        if (is_null($model_table_name)) {
            $model_table_name = $model_name . 's';
        }
        return $model_table_name;
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
                    $line .= "CHAR(40) CHARACTER SET 'utf8'";
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