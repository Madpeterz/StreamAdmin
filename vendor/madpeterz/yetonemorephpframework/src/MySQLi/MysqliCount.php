<?php

namespace YAPF\MySQLi;

abstract class MysqliCount extends MysqliSelect
{
    /**
     * selectV2
     * $where_config: see selectV2.readme
     * Note: if your table does not have an id field
     * this function will not give the results you expect
     * @return mixed[] [count => int, status => bool, message => string]
     */
    public function basicCountV2(string $table, array $whereconfig = []): array
    {
        $error_addon = ["count" => 0];
        if (strlen($table) == 0) {
            $error_msg = "No table given";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $basic_config = [
            "table" => $table,
            "fields" => ["COUNT(id) AS sqlCount"],
        ];
        $load_data = $this->selectV2($basic_config, null, $whereconfig);
        if ($load_data["status"] == false) {
            $error_msg = "No table given";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (count($load_data["dataSet"]) == 0) {
            $error_msg = "No results from SQL given";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $countData = $load_data["dataSet"][0];
        return ["status" => true, "count" => $countData["sqlCount"],"message" => "ok"];
    }
}
