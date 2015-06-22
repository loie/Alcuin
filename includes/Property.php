<?php
class Property {

    private $description;

    function __construct($description) {
        $this->description  = $description;
    }

    public function get_db_column_statement() {
        $line = '`' . $this->description->name . '` ';

        if (is_array($this->description->type)) {
            $line .= 'ENUM(';
            $items = [];
            foreach ($this->description->type as $enum) {
                array_push($items, "'" . $enum . "'");
            }
            $line .= implode(',', $items);
            $line .= ')';
        }
        else {
            switch ($this->description->type) {
                case 'string':
                    if (isset($this->description->max_length) && is_numeric($this->description->max_length)) {
                        $line .= 'VARCHAR(' . $this->description->max_length . ") CHARACTER SET 'utf8' NOT NULL";
                    } else {
                        $line .= "TEXT CHARACTER SET 'utf8' NOT NULL";
                    }
                    break;
                case 'hash':
                    $line .= "CHAR(40) CHARACTER SET 'utf8' NOT NULL";
                    break;
                case 'datetime':
                    $line .= "DATETIME NULL DEFAULT NULL";
                    break;
                case 'integer':
                    break;
                case 'float':
                    break;
                case 'bool':
                    $line .= "TINYINT(1) NULL";
                    break;
                default:
                    break;
            }
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