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
?>