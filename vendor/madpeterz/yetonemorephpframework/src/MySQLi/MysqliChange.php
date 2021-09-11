<?php

namespace YAPF\MySQLi;

abstract class MysqliChange extends MysqliProcess
{
    /**
     * removeV2
     * takes a V2 where config to remove
     * entrys from the database
     * $where_config: see selectV2.readme
     * @return mixed[] [rowsDeleted => int, status => bool, message => string]
     */
    public function removeV2(string $table, ?array $where_config = null): array
    {
        $error_addon = ["rowsDeleted" => 0];
        if (strlen($table) == 0) {
            $error_msg = "No table given";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if ($this->sqlStart() == false) {
            $error_msg = $this->getLastErrorBasic();
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $sql = "DELETE FROM " . $table . "";
        $JustDoIt = $this->processSqlRequest("", [], $error_addon, $sql, $where_config);
        if ($JustDoIt["status"] == false) {
            return $JustDoIt;
        }
        $stmt = $JustDoIt["stmt"];
        $rowsChanged = mysqli_affected_rows($this->sqlConnection);
        $stmt->close();
        if ($rowsChanged > 0) {
            $this->needToSave = true;
        }
        return ["status" => true, "rowsDeleted" => $rowsChanged, "message" => "ok"];
    }
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
            $update_config["values"][$loop] = $this->convertIfBool($update_config["values"][$loop]);
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
    /**
     * addV2
     * takes a V2 add config
     * and inserts it into the database.
     * $config = ["table" => string, "fields" => string[], "values" => mixed[], "types" => string1[]]
     * newID: is null on failure
     * rowsAdded: is 0 on failure
     * @return mixed[] [newID => ?int, rowsAdded => int, status => bool, message => string]
     */
    public function addV2($config = []): array
    {
        $error_addon = ["newID" => null, "rowsAdded" => 0];
        $required_keys = ["table", "fields","values","types"];
        foreach ($required_keys as $key) {
            if (array_key_exists($key, $config) == false) {
                $error_msg = "Required key: " . $key . " is missing";
                return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
            }
        }
        if (count($config["fields"]) != count($config["values"])) {
            $error_msg = "fields and values counts do not match!";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (count($config["values"]) != count($config["types"])) {
            $error_msg = "values and types counts do not match!";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if ($this->sqlStart() == false) {
            $error_msg = $this->getLastErrorBasic();
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $sql = "INSERT INTO " . $config["table"] . " (" . implode(', ', $config["fields"]) . ") VALUES (";
        $loop = 0;
        $bind_text = "";
        $bind_args = [];
        $addon = "";
        while ($loop < count($config["values"])) {
            $sql .= $addon;
            $value = $config["values"][$loop];
            if (($value == null) && ($value !== 0)) {
                $sql .= " NULL";
            } else {
                $sql .= "?";
                $bind_text .= $config["types"][$loop];
                $bind_args[] = $value;
            }
            $addon = " , ";
            $loop++;
        }
        $sql .= ")";
        $JustDoIt = $this->processSqlRequest($bind_text, $bind_args, $error_addon, $sql);
        if ($JustDoIt["status"] == false) {
            return $this->addError(__FILE__, __FUNCTION__, $JustDoIt["message"], $error_addon);
        }
        $stmt = $JustDoIt["stmt"];
        $newID = mysqli_insert_id($this->sqlConnection);
        $rowsAdded = mysqli_affected_rows($this->sqlConnection);
        if ($rowsAdded > 0) {
            $this->needToSave = true;
        }
        $stmt->close();
        return ["status" => true, "message" => "ok","newID" => $newID, "rowsAdded" => $rowsAdded];
    }
}
