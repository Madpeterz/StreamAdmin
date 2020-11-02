<?php

namespace YAPF\Generator;

class DbObjectsFactory extends ModelFactory
{
    public function __construct(array $defaults = [])
    {
        parent::__construct();
        echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"';
        echo ' integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z"';
        echo ' crossorigin="anonymous">';
        echo '<link rel="stylesheet" ';
        echo 'href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/darkly/bootstrap.min.css"';
        echo ' integrity="sha384-nNK9n28pDUDDgIiIqZ/MiyO3F4/9vsMtReZK39klb/MtkZI3/LtjSjlmyVPS3KdN"';
        echo ' crossorigin="anonymous">';
        foreach (GEN_DATABASES as $gen_database_name) {
            $this->processDatabaseTables($gen_database_name);
        }
    }
    public function processDatabaseTables(string $target_database): void
    {
        $this->sql->dbName = $target_database;
        echo "<h4>database: " . $target_database . "</h4>";
        echo "<table class=\"table\"><thead><tr><th>Table</th><th>Set</th><th>Single</th></tr></thead><tbody>";
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
            echo "<tr><td>Error</td><td>Unable to get tables</td><td>from db</td></tr>";
            $error_msg = "Error ~ Unable to get tables ";
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            return;
        }
        foreach ($results["dataSet"] as $row) {
            $this->CreateModel($row["TABLE_NAME"], $target_database);
        }
        echo "</tbody></table>";
    }
    /**
     * getTableColumns
     * returns the table schema.columns or null
     * @return mixed[] or null
     */
    protected function getTableColumns(string $target_table, string $target_database): ?array
    {
        $where_config = [
            "fields" => ["TABLE_SCHEMA", "TABLE_NAME"],
            "matches" => ["=","="],
            "values" => [$target_database, $target_table],
            "types" => ["s", "s"],
        ];
        $basic_config = [
            "table" => "information_schema.columns",
            "fields" => ["COLUMN_NAME","COLUMN_DEFAULT","DATA_TYPE","COLUMN_TYPE"],
        ];
        $results = $this->sql->selectV2($basic_config, null, $where_config);
        if ($results["status"] == true) {
            return $results["dataSet"];
        }
        return null;
    }
}
