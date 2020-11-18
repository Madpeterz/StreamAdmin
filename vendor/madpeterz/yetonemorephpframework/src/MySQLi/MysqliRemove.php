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
}
