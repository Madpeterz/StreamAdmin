<?php

namespace YAPF\MySQLi;

abstract class MysqliUpdate extends MysqliAdd
{
    /**
     * updateV2
     * takes a V2 update config,
     * and a V2 where config,
     * to apply a change to the database.
     * $update_config = ["fields" => string[], "values" => mixed[], "types" => string1[]]
     * $where_config: see selectV2.readme
     * @return mixed[] [rowsAdded => int, status => bool, message => string]
     */
    public function updateV2(string $table, array $update_config, array $where_config, int $expected_changes = 1): array
    {
        $error_addon = ["changes" => 0];
        if (strlen($table) == 0) {
            $error_msg = "No table given";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if ($this->sqlStart() == false) {
            $error_msg = "Unable to start SQL";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (count($update_config["types"]) == 0) {
            $error_msg = "No types given for update";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (count($update_config["fields"]) != count($update_config["values"])) {
            $error_msg = "count issue fields <=> values";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (count($update_config["values"]) != count($update_config["types"])) {
            $error_msg = "count issue values <=> types";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $bind_text = "";
        $bind_args = [];
        $sql = "UPDATE " . $table . " ";
        $loop = 0;
        $addon = "";
        while ($loop < count($update_config["values"])) {
            if ($loop == 0) {
                            $sql .= "SET ";
            }
            $sql .= $addon;
            $sql .= $update_config["fields"][$loop] . "= ";
            if (($update_config["values"][$loop] === null) && ($update_config["values"][$loop] !== 0)) {
                    $sql .= " NULL";
            } else {
                $sql .= "?";
                $bind_text .= $update_config["types"][$loop];
                $bind_args[] = $update_config["values"][$loop];
            }
            $addon = ", ";
            $loop++;
        }
        // where fields
        if (is_array($where_config) == true) {
            $failed = $this->processWhere($sql, $where_config, $bind_text, $bind_args, $failed_on, "", false);
        }
        if ($sql == "empty_in_array") {
            $error_msg = "Targeting IN|NOT IN with no array";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $JustDoIt = $this->SQLprepairBindExecute($sql, $bind_args, $bind_text);
        if ($JustDoIt["status"] == false) {
            return $this->addError(__FILE__, __FUNCTION__, $JustDoIt["message"], $error_addon);
        }
        $stmt = $JustDoIt["stmt"];
        $changes = mysqli_stmt_affected_rows($stmt);
        $stmt->close();
        if ($changes != $expected_changes) {
            $error_msg = "Unexpeected number of changes wanted " . $expected_changes . " but got:" . $changes;
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $this->needToSave = true;
        return ["status" => true,"changes" => $changes, "message" => "update ok"];
    }
}
