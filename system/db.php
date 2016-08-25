<?php
    
    const BELONGS_TO = 'belongs_to';
    const HAS_MANY = 'has_many';
    const BELONGS_TO_AND_HAS_MANY = 'belongs_to_and_has_many';

    function create_model_in_db ($configuration, $connection, $model_name) {

        $model = $configuration->architecture->models->{$model_name};
        $model_name_table = get_model_table_name($model_name, $model);

        $query_string = 'CREATE TABLE `' . $configuration->db->name . '`.`' . $model_name_table . '` (';
        $statements = [];
        // id column
        array_push($statements, "`id` INT NOT null AUTO_INCREMENT COMMENT 'Primary Key for this table' ");

        // Insert relationship columns first
        if (isset($model->relations)) {
            foreach ($model->relations as $relation_name => $relation) {
                if ($relation->type === BELONGS_TO) {
                    $column_name = $relation_name . '_id';
                    array_push($statements, '`' . $column_name . '` INT NOT NULL');
                }
            }
        }

        // other proerties columns
        if (isset($model->properties) && isset($model->properties->list)) {
            foreach ($model->properties->list as $column_name => $properties) {
                $column_line = get_db_column_statement($column_name, $properties);
                array_push($statements, $column_line);
            }
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
    }

    function create_assoc_tables ($configuration, $connection) {
        var_dump($configuration);
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
        $prepared_query = 'CREATE TABLE `{{db_name}}`.`history__{{table_name}}` LIKE `{{table_name}}`';
        $prepared_alter_query = 'ALTER TABLE `{{db_name}}`.`history__{{table_name}}`
            CHANGE `id` `id` int(10) unsigned,
            DROP PRIMARY KEY,
            ADD `_revision` bigint unsigned AUTO_INCREMENT,
            ADD `_revision_previous` bigint unsigned NULL,
            ADD `_revision_action` enum(\'INSERT\',\'UPDATE\') default NULL,
            ADD `_revision_user_id` int(10) unsigned NULL,
            ADD `_revision_timestamp` datetime NULL default NULL,
            ADD `_revision_comment` text NULL,
            ADD PRIMARY KEY (`_revision`),
            ADD INDEX (`_revision_previous`),
            ADD INDEX `org_primary` (`id`);';
        array_walk(get_object_vars($configuration->architecture->models), function ($model_name) {
            $model_properties = $configuration->architecture->models->{$model_name};
            $query = fill_with_data($prepared_query, array(
                    'db_name' => $configuration->db->name,
                    'table_name' => $model_name
                ));
            $connection->exec($query);
            $alter_query = fill_with_data($prepared_alter_query, array(
                    'db_name' => $configuration->db->name,
                    'table_name' => $model_name
                ));
            $connection->exec($query);

        });
    }

    function create_foreign_keys ($configuration, $connection) {
        $query = 'ALTER TABLE `{{db_name}}`.`{{table_name}}`  ADD CONSTRAINT `{{fk_model}}_id` FOREIGN KEY (`{{fk_model}}_id`) REFERENCES `{{db_name}}`.`{{fk_table_name}}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;';
        foreach ($configuration->architecture->models as $model_name => $model_properties) {
            // ALTER TABLE `test`.`answers` 
            //     ADD INDEX `tierchen_INDEX` (`user_id` ASC, `text` ASC, `created` ASC);
            if (isset($model_properties->relations)) {
                foreach ($model_properties->relations as $relation_name => $relation_properties) {
                    $query = null;
                    $skip = false;
                    $has_relation = false;
                    if (isset($relation_properties->type)) {
                        $model_table_name = get_model_table_name($model_name, $model_properties);
                        switch ($relation_properties->type) {
                            case BELONGS_TO:
                                // $has_relation = true;
                                // $query = 'ALTER TABLE `' .  . '`.`' . $model_table_name . '` ADD INDEX ';
                                break;
                            default:
                                break;
                        }
                    }
                }
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
            $query = str_replace('{{' . $key . '}}', $value, $query);
        }
        return $query;
    }

    // function get_db_column_index_statements() {
    //     $statement = NULL;

    //     if (isset($this->description->property->use_as_id) && is_bool($this->description->use_as_id)) {
    //         $statement = 'UNIQUE INDEX `' . $this->description->name . '_UNIQUE` (`' . $this->description->name . '` ASC)';
    //     }
    //     return $statement;
    // }


        // if (isset($model->belongs_to) && is_array($model->belongs_to)) {
        //     foreach ($model->belongs_to as $relation) {
        //         $relation_name = null;
        //         $column_name = null;
        //         $index_name = null;
        //         $table_name = null;
        //         if (is_object($relation)) {
        //             $relation_name = $relation->name;
        //             $column_name = $relation->name . '_id';
        //             $table_name = $relation->model;
        //         }
        //         else if (is_string($relation)) {
        //             $relation_name = $relation;
        //             $column_name = $relation . '_id';
        //             $table_name = $relation;
        //         }
        //         $index_name = $column_name . '_INDEX';
        //         array_push($statements, '`' . $column_name . '` INT NOT null');
        //         // array_push($index_statements, 'INDEX `' . $index_name . '` (`' . $column_name . '` ASC)');
        //         // array_push($constraint_statements, 'FOREIGN KEY (`' . $column_name . '`) REFERENCES `' . $this->db_conf->name . '`.`' . $table_name . 's` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        //     }
        // }

    // array_push($index_statements, $index_line);
            // $line = "`token_last_updated` DATETIME NULL DEFAULT '0000-00-00 00:00:00'";
            // $index_line = 'INDEX `token_last_updated_INDEX` (`token_last_updated` ASC)';
            // array_push($statements, $line);
            // array_push($index_statements, $index_line);


        // // create as_bs table for a->belongs_to_and_has_many(b)
        // if (isset($model->belongs_to_and_has_many) && is_array($model->belongs_to_and_has_many)) {
        //     foreach($model->belongs_to_and_has_many as $relation) {
        //         $relation_model = new stdClass();
        //         if (is_string($relation)) {
        //             $relation_model->name = $model->name . 's_' . $relation;
        //         }
        //         else if (is_array($relation)) {
        //             $relation_model->name = $model->name . 's_' . $relation->name;
        //         }
        //         $relation_model->belongs_to = [$model->name, $relation];
        //         create_model_in_db($relation_model, false, false);
        //     }
        // }


        // insert 
        // if (isset($model->instances) && is_array($model->instances)) {
        //     foreach ($model->instances as $column_name => $properties) {
        //         $insert_statement = "INSERT INTO " . $model_name . " ({keys}) VALUES ({values})";
        //         $column_names = array();
        //         $propertiess = array();
        //         foreach (get_object_vars($properties) as $key => $properties) {
        //             array_push($column_names, $key);
        //             array_push($propertiess, "'" . $properties . "'");
        //             $insert_statement = str_replace('{keys}', implode(',', $column_names), $insert_statement);
        //             $insert_statement = str_replace('{values}', implode(',', $properties), $insert_statement);
        //             $connection->exec($insert_statement);
        //         }
        //     }
        // }

    // $index_line = 'INDEX `token_INDEX` (`token` ASC)';
        // $all_statements = array_merge($statements, $index_statements, $constraint_statements);
?>