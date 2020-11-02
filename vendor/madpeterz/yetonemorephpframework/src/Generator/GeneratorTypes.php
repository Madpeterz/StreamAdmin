<?php

namespace YAPF\Generator;

use YAPF\Core\SqlConnectedClass as SqlConnectedClass;

abstract class GeneratorTypes extends SqlConnectedClass
{
    /**
     * getColType
     * returns the col type for the selected target_table
     */
    public function getColType(
        string $target_type,
        string $col_type,
        string $table,
        string $colname
    ): string {
        if (in_array($target_type, $this->known_types) == false) {
            $error_msg = "Table: " . $table . " Column: " . $colname . " unknown type: ";
            $error_msg .= $target_type . " defaulting to string!<br/>";
            echo $error_msg;
            echo "<br/>";
            return "str";
        }
        if (in_array($target_type, $this->int_types)) {
            if (strpos($col_type, 'tinyint(1)') !== false) {
                return "bool";
            }
            return "int";
        }
        if (in_array($target_type, $this->float_types)) {
            return "float";
        }
        return "str";
    }
}
