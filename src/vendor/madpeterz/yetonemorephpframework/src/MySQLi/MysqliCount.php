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
    public function basicCountV2(string $table, array $whereconfig = null): array
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
            return $this->addError(__FILE__, __FUNCTION__, $load_data["message"], $error_addon);
        }
        return ["status" => true, "count" => $load_data["dataset"][0]["sqlCount"] ,"message" => "ok"];
    }
}
