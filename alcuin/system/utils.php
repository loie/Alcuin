<?php
    require 'autoload.php';

    const BELONGS_TO = 'belongs_to';
    const HAS_MANY = 'has_many';
    const BELONGS_TO_AND_HAS_MANY = 'belongs_to_and_has_many';
    
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

    function array_to_string ($arr, $base = 0) {
        $output = json_decode(str_replace(array('(',')'), array('&#40','&#41'), json_encode($arr)), true);
        $encoder = new \Riimu\Kit\PHPEncoder\PHPEncoder();
        $output = $encoder->encode($arr, [
            'array.base' => 0,
            'array.short' => true,
            'array.inline' => false,
            'array.omit' => true,
            'array.indent' => 4,
            'boolean.capitalize' => false,
            'null.capitalize' => false,
            'string.escape' => false,
            'object.format' => 'export'
        ]);
        // $output = str_replace(array('array (',')','&#40','&#41'), array('[',']','(',')'), $output);
        return $output;
    }

    function copy_with_data ($filename_source, $filename_target, $replacements = []) {
        assert(file_exists($filename_source));
        $file_content = file_get_contents($filename_source);
        $file_content_filled = $file_content;
        array_walk($replacements, function ($replacement, $key) use (&$file_content_filled) {
            $replace_with = $replacement;
            if (is_array($replacement)) {
                // use to get PHP 5.4 style output
                $replace_with = array_to_string($replacement);
            } else if (is_object($replacement)) {
                $replace_with = '';
                foreach ($replacement->replacements as $replace_set) {
                    $replace_string = $replacement->template;
                    foreach ($replace_set as $inner_replacement_key => $inner_replacement_value) {
                        $replace_string = str_replace(
                            '{{' . $inner_replacement_key . '}}',
                            $inner_replacement_value,
                            $replace_string);
                    }
                    $replace_with .= $replace_string;
                }
            }
            $file_content_filled = str_replace('{{' . $key . '}}', $replace_with, $file_content_filled);
        });
        $dir_name_arr = explode('/', $filename_target);
        $dir_name = implode('/', array_slice($dir_name_arr, 0, -1));
        if (!is_dir($dir_name)) {
            // dir doesn't exist, make it
            mkdir($dir_name);
        }
        file_put_contents($filename_target, $file_content_filled);
    }

    /* Get table name for model */
    function get_model_plural_name ($model_name, $model_properties) {
        $model_table_name = null;
        if (is_object($model_properties)) {
            $model_table_name = isset($model_properties->name_plural) ?
                $model_properties->name_plural : NULL;
            
        }
        if (is_null($model_table_name)) {
            $model_table_name = $model_name . 's';
        }
        return $model_table_name;
    }
?>