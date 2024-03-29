<?php

namespace YAPF\Generator;

class DbObjectsFactory extends ModelFactory
{
    public function __construct($autoStart = true)
    {
        parent::__construct();
        if ($autoStart == true) {
            $this->start();
        }
    }
    public function setOutputToHTML(): void
    {
        $this->use_output = true;
        $this->console_output = false;
    }
    public function start(): void
    {
        global $GEN_DATABASES;
        if ($this->use_output == true) {
            if ($this->console_output == false) {
                $this->output .=  '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/';
                $this->output .=  'bootstrap/4.5.2/css/bootstrap.min.css"';
                $this->output .=  ' integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z"';
                $this->output .=  ' crossorigin="anonymous">';
                $this->output .=  '<link rel="stylesheet" ';
                $this->output .=  'href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/darkly/bootstrap.min.css"';
                $this->output .=  ' integrity="sha384-nNK9n28pDUDDgIiIqZ/MiyO3F4/9vsMtReZK39klb/MtkZI3/LtjSjlmyVPS3KdN"';
                $this->output .=  ' crossorigin="anonymous">';
            }
        }
        if (isset($GEN_DATABASES) == true) {
            if (count($GEN_DATABASES) > 0) {
                foreach ($GEN_DATABASES as $gen_database_name) {
                    $this->processDatabaseTables($gen_database_name);
                }
            }
        }
    }
    public function processDatabaseTables(string $target_database): void
    {
        global $GEN_SELECTED_TABLES_ONLY;
        $this->sql->dbName = $target_database;
        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo "Starting database: " . $target_database . "\n";
            } else {
                $this->output .= "<h4>database: " . $target_database . "</h4>";
                $this->output .= "<table class=\"table\"><thead><tr><th>Table</th>";
                $this->output .= "<th>Set</th><th>Single</th></tr></thead><tbody>";
            }
        }
        $where_config = [
            "fields" => ["TABLE_SCHEMA"],
            "matches" => ["="],
            "values" => [$target_database],
            "types" => ["s"],
        ];
        $basic_config = [
            "table" => "information_schema.tables",
            "fields" => ["TABLE_NAME"],
        ];
        $results = $this->sql->selectV2($basic_config, null, $where_config);
        if ($results["status"] == false) {
            if ($this->use_output == true) {
                if ($this->console_output == true) {
                    echo "\033[31mError: Unable to get tables from DB\033[0m\n";
                } else {
                    $this->output .= "<tr><td>Error</td><td>Unable to get tables</td><td>from db</td></tr>";
                }
            }
            $error_msg = "Error ~ Unable to get tables for " . $target_database . "";
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            return;
        }
        foreach ($results["dataset"] as $row) {
            $process = true;
            if (isset($GEN_SELECTED_TABLES_ONLY) == true) {
                if (is_array($GEN_SELECTED_TABLES_ONLY) == true) {
                    $process = in_array($row["TABLE_NAME"], $GEN_SELECTED_TABLES_ONLY);
                }
            }
            if ($process == true) {
                $this->CreateModel($row["TABLE_NAME"], $target_database);
            } else {
                if ($this->console_output == true) {
                    echo "Skipped table: " . $row["TABLE_NAME"] . "\n";
                } else {
                    $this->output .= "<tr><td>" . $row["TABLE_NAME"] . "</td><td>Skipped</td><td>Skipped</td></tr>";
                }
            }
        }
        if ($this->use_output == true) {
            if ($this->console_output == true) {
                echo "finished database \n";
            } else {
                $this->output .= "</tbody></table>";
            }
        }
    }
}
