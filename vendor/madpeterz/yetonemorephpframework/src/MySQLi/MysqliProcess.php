<?php

namespace YAPF\MySQLi;

use Exception;

abstract class MysqliProcess extends MysqliOptions
{
    /**
     * processSqlRequest
     * Do stuff then talk to Sql.
     * @return mixed[] [status => bool, message => string, "stm" => false|statement object]
     */
    protected function processSqlRequest(
        string $bind_text,
        array $bind_args,
        array $error_addon,
        string &$sql,
        ?array $where_config = null,
        ?array $order_config = null,
        ?array $options_config = null,
        ?array $join_tables = null
    ): array {
        $error_addon["stmt"] = false;
        $failed = false;
        $failed_on = "";

        $this->selectBuildJoins($join_tables, $sql, $failed, $failed_on);
        if ($failed == true) {
            $error_msg = "failed with message:" . $failed_on;
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }

        $failed = !$this->processWhere($sql, $where_config, $bind_text, $bind_args, $failed_on, $failed);
        if ($failed == true) {
            $error_msg = "Where config failed: " . $failed_on;
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if ($sql == "empty_in_array") {
            $error_msg = "Targeting IN|NOT IN with no array";
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (is_array($order_config) == true) {
            $this->buildOrderby($sql, $order_config);
        }
        if (is_array($options_config) == true) {
            $this->buildOption($sql, $options_config);
        }
        return $this->SQLprepairBindExecute($error_addon, $sql, $bind_args, $bind_text);
    }
}
