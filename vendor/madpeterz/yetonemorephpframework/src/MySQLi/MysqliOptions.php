<?php

namespace YAPF\MySQLi;

abstract class MysqliOptions extends MysqliWhere
{
    /**
     * buildOrderby
     * returns the last SQL statement processed
     * good if you want to check what its doing
     */
    protected function buildOrderby(
        string &$sql,
        array $order
    ): void {
        if (array_key_exists("ordering_enabled", $order) == true) {
            if (array_key_exists("order_field", $order) == false) {
                $order["order_field"] = "id";
                $order["order_dir"] = "DESC";
                $order["ordering_enabled"] = true;
            }
        }
        if ($order["ordering_enabled"] == true) {
            if (array_key_exists("as_string", $order) == true) {
                $sql .= " ORDER BY " . $order["as_string"] . " ";
            } else {
                $sql .= " ORDER BY " . $order["order_field"] . " " . $order["order_dir"] . " ";
            }
        }
    }
    /**
     * buildSelectOption
     * processes the options settings for
     * max_entrys and page_number to create the LIMIT sql.
     */
    protected function buildOption(string &$sql, array $options): void
    {
        if (array_key_exists("groupby", $options) == true) {
            $sql .= " GROUP BY " . $options["groupby"];
        }
        if (array_key_exists("max_entrys", $options) == true) {
            if (array_key_exists("page_number", $options) == true) {
                if ($options["page_number"] > 0) {
                    $offset = $options["page_number"] * $options["max_entrys"];
                    $sql .= " LIMIT " . $options["max_entrys"] . " OFFSET " . $offset;
                } elseif ($options["max_entrys"] > 0) {
                    $sql .= " LIMIT " . $options["max_entrys"];
                }
            } else {
                if ($options["max_entrys"] > 0) {
                    $sql .= " LIMIT " . $options["max_entrys"];
                }
            }
        }
    }
}
