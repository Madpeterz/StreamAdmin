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
    public function start(): void
    {
        if ($this->use_output == true) {
            $this->output .=  '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/';
            $this->output .=  'bootstrap/4.5.2/css/bootstrap.min.css"';
            $this->output .=  ' integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z"';
            $this->output .=  ' crossorigin="anonymous">';
            $this->output .=  '<link rel="stylesheet" ';
            $this->output .=  'href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/darkly/bootstrap.min.css"';
            $this->output .=  ' integrity="sha384-nNK9n28pDUDDgIiIqZ/MiyO3F4/9vsMtReZK39klb/MtkZI3/LtjSjlmyVPS3KdN"';
            $this->output .=  ' crossorigin="anonymous">';
        }
        if (defined("GEN_DATABASES") == true) {
            if (count(GEN_DATABASES) > 0) {
                foreach (GEN_DATABASES as $gen_database_name) {
                    $this->processDatabaseTables($gen_database_name);
                }
            }
        }
    }
    public function processDatabaseTables(string $target_database): void
    {
        $this->sql->dbName = $target_database;
        if ($this->use_output == true) {
            $this->output .= "<h4>database: " . $target_database . "</h4>";
            $this->output .= "<table class=\"table\"><thead><tr><th>Table</th>";
            $this->output .= "<th>Set</th><th>Single</th></tr></thead><tbody>";
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
                $this->output .= "<tr><td>Error</td><td>Unable to get tables</td><td>from db</td></tr>";
            }
            $error_msg = "Error ~ Unable to get tables for " . $target_database . "";
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            return;
        }
        foreach ($results["dataset"] as $row) {
            $NoTablesInScema = false;
            $this->CreateModel($row["TABLE_NAME"], $target_database);
        }
        if ($this->use_output == true) {
            $this->output .= "</tbody></table>";
        }
    }
}
