<?php

namespace YAPF\MySQLi;

abstract class MysqliAdd extends MysqliProcess
{
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
