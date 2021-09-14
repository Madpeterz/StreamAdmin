<?php

namespace YAPF\MySQLi;

abstract class MysqliQuery extends MysqliChange
{
    protected int $sql_selects = 0;
    public function getSQLselectsCount(): int
    {
        return $this->sql_selects;
    }

    /**
     * selectV2
     * for a full breakdown of all the magic
     * please see the selectV2.readme
     * @return mixed[] [dataset => mixed[mixed[]], status => bool, message => string]
     */
    public function selectV2(
        array $basic_config,
        ?array $order_config = null,
        ?array $where_config = null,
        ?array $options_config = null,
        ?array $join_tables = null,
        bool $clean_ids = false
    ): array {
        $error_addon = ["dataset" => []];
        if (array_key_exists("table", $basic_config) == false) {
            $error_msg = "table index missing from basic config!";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (strlen($basic_config["table"]) == 0) {
            $error_msg = "No table set in basic config!";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if ($this->sqlStart() == false) {
            $error_msg = $this->getLastErrorBasic();
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $main_table_id = "";
        $auto_ids = false;

        $this->selectBuildTableIds($join_tables, $main_table_id, $auto_ids, $clean_ids);
        $sql = "SELECT ";
        $this->selectBuildFields($sql, $basic_config);
        $sql .= " FROM " . $basic_config["table"] . " " . $main_table_id . " ";
        $JustDoIt = $this->processSqlRequest(
            "",
            [],
            $error_addon,
            $sql,
            $where_config,
            $order_config,
            $options_config,
            $join_tables
        );
        if ($JustDoIt["status"] == false) {
            return $JustDoIt;
        }
        $stmt = $JustDoIt["stmt"];
        $result = $stmt->get_result();
        $stmt->close();
        $dataset = $this->buildDataset($clean_ids, $result);
        $this->sql_selects++;
        return ["status" => true, "dataset" => $dataset ,"message" => "ok"];
    }
    /**
     *  buildDataset
     *  $result expects mysqli_result or false
     *  @return mixed[] returns the dataset in keyValue pairs or a empty array
     */
    protected function buildDataset(bool $clean_ids, $result): array
    {
        $dataset = [];
        if ($result == false) {
            return $dataset;
        }
        if ($clean_ids == true) {
            while ($entry = $result->fetch_assoc()) {
                $dataset[] = $entry;
            }
            return $dataset;
        }
        $loop = 0;
        while ($entry = $result->fetch_assoc()) {
            $cleaned_entry = [];
            foreach ($entry as $field => $value) {
                $cleaned_entry[$field] = $value;
            }
            $dataset[] = $cleaned_entry;
            $loop++;
        }
        return $dataset;
    }
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
            $error_msg = $this->getLastErrorBasic();
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $bind_args = [];
        $bind_text = "";
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
                $bind_args[] = $match_value;
                $bind_text .= $match_type;
            }
            $table_id++;
        }
        $sql .= " ORDER BY id DESC";
        $JustDoIt = $this->SQLprepairBindExecute($error_addon, $sql, $bind_args, $bind_text);
        if ($JustDoIt["status"] == false) {
            return $JustDoIt;
        }
        $stmt = $JustDoIt["stmt"];
        $result = $stmt->get_result();
        $dataset = [];
        $loop = 0;
        while ($entry = $result->fetch_assoc()) {
            $dataset[$loop] = $entry;
            $loop++;
        }
        return ["status" => true, "dataset" => $dataset ,"message" => "ok"];
    }
}
