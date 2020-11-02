<?php

namespace YAPF\MySQLi;

abstract class MysqliRemove extends MysqliUpdate
{
    /**
     * removeV2
     * takes a V2 where config to remove
     * entrys from the database
     * $where_config: see selectV2.readme
     * @return mixed[] [rowsDeleted => int, status => bool, message => string]
     */
    public function removeV2(string $table, array $where_config): array
    {
        $error_addon = ["rowsDeleted" => 0];
        if (strlen($table) == 0) {
            $error_msg = "No table given";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if ($this->sqlStart() == false) {
            $error_msg = "Unable to start SQL";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        $bind_text = "";
        $bind_args = [];
        $sql = "DELETE FROM " . $table . "";
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
        $rowsChanged = mysqli_affected_rows($this->sqlConnection);
        $stmt->close();
        if ($rowsChanged > 0) {
            $this->needToSave = true;
        }
        return ["status" => true, "rowsDeleted" => $rowsChanged];
    }
}
