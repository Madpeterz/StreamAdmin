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
     * @return mixed[] [changes => int, status => bool, message => string]
     */
    public function updateV2(string $table, array $update_config, ?array $where_config = null): array
    {
        $error_addon = ["changes" => 0];
        if (strlen($table) == 0) {
            $error_msg = "No table given";
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
        if ($this->sqlStart() == false) {
            $error_msg = $this->getLastErrorBasic();
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
            $sql .= $update_config["fields"][$loop] . "=";
            if (($update_config["values"][$loop] == null) && ($update_config["values"][$loop] !== 0)) {
                $sql .= "NULL";
            } else {
                $sql .= "?";
                $bind_text .= $update_config["types"][$loop];
                $bind_args[] = $update_config["values"][$loop];
            }
            $addon = ", ";
            $loop++;
        }
        // where fields
        $JustDoIt = $this->processSqlRequest($bind_text, $bind_args, $error_addon, $sql, $where_config);
        if ($JustDoIt["status"] == false) {
            return $JustDoIt;
        }
        $stmt = $JustDoIt["stmt"];
        $changes = mysqli_stmt_affected_rows($stmt);
        $stmt->close();
        $this->needToSave = true;
        return ["status" => true,"changes" => $changes, "message" => "ok"];
    }
}
