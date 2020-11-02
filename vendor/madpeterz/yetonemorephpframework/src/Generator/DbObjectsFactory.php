<?php

namespace YAPF\Generator;

class DbObjectsFactory extends GeneratorModels
{
    public function __construct(array $defaults = [])
    {
        parent::__construct();
        foreach (GEN_DATABASES as $gen_database_name) {
            $this->processDatabaseTables($gen_database_name);
        }
    }
    public function processDatabaseTables(string $target_database): void
    {
        $this->sql->dbName = $target_database;
        echo "Starting database: " . $target_database . " <br/>";
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
        $results = $sql->selectV2($basic_config, null, $where_config);
        if ($results["status"] == false) {
            $error_msg = "Error ~ Unable to get tables from db";
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            echo $error_msg;
            return;
        }
        foreach ($results["dataSet"] as $row) {
            $this->CreateModel($row["TABLE_NAME"], $target_database);
        }
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
        $results = $sql->selectV2($basic_config, null, $where_config);
        if ($results["status"] == true) {
            return $results["dataSet"];
        }
        return null;
    }
}
