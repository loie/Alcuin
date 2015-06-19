<?php
class Property {

    private $description;

    function __construct($description) {
        $this->description  = $description;
    }

    public function get_db_column_statement() {
        $line = '`' . $this->description->name . '` ';

        switch ($this->description->type) {
            case 'string':
                if (isset($this->description->max_length) && is_numeric($this->description->max_length)) {
                    $line .= 'VARCHAR(' . $this->description->max_length . ") CHARACTER SET 'utf8' NOT NULL";
                } else {

                }
                break;
            case 'hash':
                $line .= "VARCHAR(40) CHARACTER SET 'utf8' NOT NULL";
                break;
            case 'datetime':
                break;
            case 'integer':
                break;
            case 'float':
                break;
            case 'bool':
                break;
            default:
                break;
        }

        return $line;
    }

    public function get_db_column_index_statements() {
        $statement = NULL;

        if (isset($this->description->property->use_as_id) && is_bool($this->description->use_as_id)) {
            $statement = 'UNIQUE INDEX `' . $this->description->name . '_UNIQUE` (`' . $this->description->name . '` ASC)';
        }
        return $statement;
        
    }
}
?>