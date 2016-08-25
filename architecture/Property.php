<?php
class Property {

    private $description;

    function __construct($description) {
        $this->description  = $description;
    }

    private function is_null() {
        return $this->description->null_allowed === true;
    }

    
}
?>