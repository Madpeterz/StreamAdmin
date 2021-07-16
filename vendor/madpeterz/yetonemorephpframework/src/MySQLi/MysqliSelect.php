<?php

namespace YAPF\MySQLi;

abstract class MysqliSelect extends MysqliRemove
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
        $dataset = [];
        if ($clean_ids == true) {
            $dataset = [];
            while ($entry = $result->fetch_assoc()) {
                $dataset[] = $entry;
            }
        } else {
            $loop = 0;
            while ($entry = $result->fetch_assoc()) {
                $cleaned_entry = [];
                foreach ($entry as $field => $value) {
                    $cleaned_entry[$field] = $value;
                }
                $dataset[] = $cleaned_entry;
                $loop++;
            }
        }
        $this->sql_selects++;
        return ["status" => true, "dataset" => $dataset ,"message" => "ok"];
    }
}
