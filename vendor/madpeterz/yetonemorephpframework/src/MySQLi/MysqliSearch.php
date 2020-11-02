<?php

namespace YAPF\MySQLi;

abstract class MysqliSearch extends MysqliCount
{
    /**
     * searchTables
     * searchs multiple tables to find a match
     * returns an array in the dataset of the following
     * [targetfield => value, source => table found in]
     * @return mixed[] [dataset => mixed[mixed[]], status => bool, message => string]
     */
    public function searchTables(
        array $target_tables,
        string $match_field,
        $match_value,
        string $match_type = "s",
        string $match_code = "=",
        int $limit_amount = 1,
        string $target_field = "id"
    ): array {
        $error_addon = ["dataset" => []];
        if (count($target_tables) <= 1) {
            $error_msg = "Requires 2 or more tables to use search";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (strlen($match_field) == 0) {
            $error_msg = "Requires a match field to be sent";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (in_array($match_type, ["s", "d", "i", "b"]) == false) {
            $error_msg = "Match type is not vaild";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $match_symbol = "?";
        if ($match_value === null) {
            $match_symbol = "NULL";
            if (in_array($match_code, ["IS","IS NOT"]) == false) {
                $error_msg = "Match value can not be null";
                return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
            }
        }
        if ($this->sqlStart() == false) {
            $error_msg = "Unable to start SQL";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $bind_values = [];
        $sql = "";
        $addon = "";
        $table_id = 1;
        foreach ($target_tables as $table) {
            $sql .= $addon;
            $sql .= "(SELECT tb" . $table_id . "." . $target_field . ", '";
            $sql .= $table . "' AS source FROM " . $table . " tb" . $table_id . "";
            $sql .= " WHERE tb" . $table_id . "." . $match_field . " " . $match_code . " " . $match_symbol . " ";
            if ($limit_amount > 0) {
                $sql .= "LIMIT " . $limit_amount;
            }
            $sql .= ")";
            $addon = " UNION ALL ";
            if ($match_symbol == "?") {
                $bind_values[] = [$match_value => $match_type];
            }
            $table_id++;
        }
        $sql .= " ORDER BY id DESC";
        $JustDoIt = $this->SQLprepairBindExecute($sql, $bind_args, $bind_text);
        if ($JustDoIt["status"] == false) {
            return $this->addError(__FILE__, __FUNCTION__, $JustDoIt["message"], $error_addon);
        }
        $stmt = $JustDoIt["stmt"];
        $result = $stmt->get_result();
        $dataSet = [];
        $loop = 0;
        while ($entry = $result->fetch_assoc()) {
            $dataSet[$loop] = $entry;
            $loop++;
        }
        return ["status" => true, "dataSet" => $dataSet ,"message" => "ok"];
    }
}
