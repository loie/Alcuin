<?php
    
    // checks if all elements in required_properties array exist in object
    function get_missing_prop($obj, $required_properties) {
        assert (is_array($required_properties));
        foreach ($required_properties as $prop) {
            if ($obj->{$prop} == null) {
                return $prop;
            }
        }
        return null;
    }

    function copy_with_data ($filename_source, $filename_target, $replacements = []) {
        assert(file_exists($filename_source));
        $file_content = file_get_contents($filename_source);
        $file_content_filled = $file_content;
        array_walk($replacements, function ($replacement, $key) use (&$file_content_filled) {
            $replace_with = $replacement;
            if (is_array($replacement)) {
                $replace_with = var_export($replacement, true);
            } else if (is_object($replacement)) {
                $created_string = '';
                foreach ($replacement->replacements as $replacement_name => $replaces) {
                    foreach ($replaces as $one_replace) {
                        $created_string .= str_replace(
                            '{{' . $replacement_name . '}}',
                            ucfirst($one_replace),
                            $replacement->template);
                    }
                }
                $replace_with = $created_string;
            }
            $file_content_filled = str_replace('{{' . $key . '}}', $replace_with, $file_content_filled);
        });
        file_put_contents($filename_target, $file_content_filled);
    }

    /* Get table name for model */
    function get_model_plural_name ($model_name, $model_properties) {
        $model_table_name = null;
        if (is_object($model_properties)) {
            $model_table_name = $model->name_plural;
        }
        if (is_null($model_table_name)) {
            $model_table_name = $model_name . 's';
        }
        return $model_table_name;
    }
?>