<?php

namespace YAPF\Generator;

use YAPF\Core\SQLi\SqlConnectedClass as SqlConnectedClass;

abstract class GeneratorTypes extends SqlConnectedClass
{
    public function __construct()
    {
        parent::__construct();
        if (defined('STDIN') == true) {
            $this->console_output = true;
        }
    }
    protected $counter_models_created = 0;
    protected $counter_models_failed = 0;
    protected $use_output = true;
    protected $console_output = false;
    public function noOutput(): void
    {
        $this->use_output = false;
    }
    public function getModelsCreated(): int
    {
        return $this->counter_models_created;
    }
    public function getModelsFailed(): int
    {
        return $this->counter_models_failed;
    }
    /**
     * getColType
     * returns the col type for the selected target_table
     */
    protected function getColType(
        string $target_type,
        string $col_type,
        string $table,
        string $colname
    ): string {
        if (in_array($target_type, $this->known_types) == false) {
            $error_msg = "Table: " . $table . " Column: " . $colname . " unknown type: ";
            $error_msg .= $target_type . " defaulting to string!<br/>";
            if ($this->use_output == true) {
                if ($this->console_output == true) {
                    echo "Error: " . $error_msg . " \n";
                } else {
                    $this->output .=  $error_msg;
                    $this->output .=  "<br/>";
                }
            }
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
