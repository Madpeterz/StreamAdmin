<?php

namespace YAPF\MySQLi;

abstract class MysqliSelect extends MysqliRemove
{
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
        ?array $join_tables = null
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
            $error_msg = "Unable to start SQL";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $main_table_id = "";
        $auto_ids = false;
        $clean_ids = false;
        if (is_array($join_tables) == true) {
            $main_table_id = "mtb";
            $auto_ids = true;
            $clean_ids = true;
            if (array_key_exists("main_table_id", $join_tables) == true) {
                $main_table_id = $join_tables["main_table_id"];
            }
            if (array_key_exists("autoids", $join_tables) == true) {
                $auto_ids = $join_tables["autoids"];
            }
            if (array_key_exists("cleanids", $join_tables) == true) {
                $clean_ids = $join_tables["cleanids"];
            }
        }
        $failed = false;
        $failed_on = "";
        $sql = "SELECT ";
        if (array_key_exists("fields", $basic_config) == true) {
            if (array_key_exists("field_function", $basic_config) == true) {
                $loop = 0;
                $addon = "";
                foreach ($basic_config["fields"] as $field) {
                    $sql .= $addon;
                    if (count($basic_config["field_function"]) > $loop) {
                        $sql .= " " . $basic_config["field_function"][$loop] . "( ";
                    }
                    if (($main_table_id != "") && ($auto_ids == true)) {
                        $sql .= " " . $main_table_id . "." . $field . "";
                    } else {
                        $sql .= " " . $field . "";
                    }
                    if (count($basic_config["field_function"]) > $loop) {
                        $sql .= " )";
                    }
                    $addon = " , ";
                    $loop++;
                }
            } else {
                if (($main_table_id != "") && ($auto_ids == true)) {
                    $sql .= " " . $main_table_id . "." . implode(", " . $main_table_id . ".", $basic_config["fields"]);
                } else {
                    $sql .= " " . implode(", ", $basic_config["fields"]);
                    $clean_ids = false;
                }
            }
        } else {
            $clean_ids = false;
            if ($main_table_id != "") {
                $sql .= " " . $main_table_id . ".*";
            } else {
                $sql .= " *";
            }
        }
        $sql .= " FROM " . $basic_config["table"] . " " . $main_table_id . " ";
        if ($main_table_id != "") {
            $failed = true;
            $all_found = true;
            $counts_match = true;
            $required_keys = ["tables","types","onfield_left","onfield_match","onfield_right"];
            foreach ($required_keys as $key) {
                if (array_key_exists("tables", $join_tables) == false) {
                    $all_found = false;
                    break;
                }
            }
            if ($all_found == true) {
                $last_key = "";
                foreach ($required_keys as $key) {
                    if ($last_key != "") {
                        if (count($join_tables[$key]) != count($join_tables[$last_key])) {
                            $counts_match = false;
                            break;
                        }
                    }
                    $last_key  = $key;
                }
            }
            if (($all_found == true) && ($counts_match == true)) {
                $failed = false;
                $loop = 0;
                while ($loop < count($join_tables["tables"])) {
                    $sql .= " " . $join_tables["types"][$loop] . " " . $join_tables["tables"][$loop] . "";
                    $sql .= " ON " . $join_tables["onfield_left"][$loop] . " ";
                    $sql .= $join_tables["onfield_match"][$loop] . " " . $join_tables["onfield_right"][$loop] . "";
                    $loop++;
                }
            }
        }
        $bind_text = "";
        $bind_args = [];
        if ($failed == false) {
            if (is_array($where_config) == true) {
                $this->processWhere($sql, $where_config, $bind_text, $bind_args, $failed_on, $main_table_id, $auto_ids);
            }
        }
        if ($failed == true) {
            $error_msg = "failed with message:" . $failed_on;
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if ($sql == "empty_in_array") {
            $error_msg = "Targeting IN|NOT IN with no array";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (is_array($order_config) == true) {
            $this->buildSelectOrderby($sql, $order_config, $main_table_id, $auto_ids);
        }
        if (is_array($options_config) == true) {
            $this->buildSelectOption($sql, $options_config);
        }
        $JustDoIt = $this->SQLprepairBindExecute($sql, $bind_args, $bind_text);
        if ($JustDoIt["status"] == false) {
            return $this->addError(__FILE__, __FUNCTION__, $JustDoIt["message"], $error_addon);
        }
        $stmt = $JustDoIt["stmt"];
        $result = $stmt->get_result();
        $stmt->close();
        $dataSet = [];
        if ($clean_ids == true) {
            $dataSet = [];
            while ($entry = $result->fetch_assoc()) {
                $dataSet[] = $entry;
            }
        } else {
            $loop = 0;
            while ($entry = $result->fetch_assoc()) {
                $cleaned_entry = [];
                foreach ($entry as $field => $value) {
                    $field_name_bits = explode(".", $field);
                    if (count($field_name_bits) > 1) {
                        $cleaned_entry[$field_name_bits[1]] = $value;
                    } else {
                        $cleaned_entry[$field] = $value;
                    }
                }
                $dataSet[] = $cleaned_entry;
                $loop++;
            }
        }
        return ["status" => true, "dataSet" => $dataSet ,"message" => "ok"];
    }
}
