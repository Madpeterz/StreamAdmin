<?php

namespace YAPF\Generator;

use YAPF\SqlConnectedClass as SqlConnectedClass;

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
        global $string_types, $int_types, $float_types, $known_types;
        if (in_array($known_types, $target_type) == false) {
            $error_msg = "Table: " . $table . " Column: " . $colname . " unknown type: ";
            $error_msg .= $target_type . " defaulting to string!<br/>";
            echo $error_msg;
            echo "<br/>";
            return "str";
        }
        if (in_array($target_type, $int_types)) {
            if (strpos($col_type, 'tinyint(1)') !== false) {
                return "bool";
            }
            return "int";
        }
        if (in_array($target_type, $float_types)) {
            return "float";
        }
        return "str";
    }
}
