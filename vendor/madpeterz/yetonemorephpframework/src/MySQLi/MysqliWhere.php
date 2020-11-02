<?php

namespace YAPF\MySQLi;

abstract class MysqliWhere extends MysqliFunctions
{
    /**
     * processWhere
     * processes the where_config to make
     * sure its vaild and setup fully before passing
     * it over to the builder
     * returns the result of the builder if all is ok
     * returns false if something failed and is on fire
     */
    protected function processWhere(
        string &$sql,
        ?array $where_config,
        string &$bind_text,
        array &$bind_args,
        string &$failed_on,
        string $main_table_id = "",
        bool $auto_ids = false
    ): bool {
        if ($where_config == null) {
            $failed_on = "Where config is null";
            return false;
        } elseif (is_array($where_config) == false) {
            $failed_on = "Where config is not an array";
            return false;
        }
        $failed = false;
        $missing_keys_text = "";
        $check_keys = ["fields","values","types","matches"];
        $missing_keys = [];
        foreach ($check_keys as $test_key) {
            if (array_key_exists($test_key, $where_config) == false) {
                $missing_keys[] = $test_key;
            }
        }
        if (count($missing_keys) > 0) {
            $failed = true;
            $missing_keys_text = " ~ " . implode(",", $missing_keys);
        } elseif (array_key_exists("join_with", $where_config) == false) {
            $where_config["join_with"] = "AND";
        } elseif ($failed == true) {
            $failed_on = "Required where_config keys missing " . $missing_keys_text;
            return false;
        } elseif (count($where_config["fields"]) != count($where_config["values"])) {
            $failed_on = "count error fields <=> values";
            return false;
        } elseif (count($where_config["values"]) != count($where_config["types"])) {
            $failed_on = "count error values <=> types";
            return false;
        } elseif (count($where_config["types"]) != count($where_config["matches"])) {
            $failed_on = "count error types <=> matches";
            return false;
        } elseif (count($where_config["fields"]) == 0) {
            $failed_on = "where fields is empty - accepting this is risky so im not";
            return false;
        }
        if (is_array($where_config["join_with"]) == false) {
            $new_array = [];
            $loop = 1;
            while ($loop < count($where_config["types"])) {
                $new_array[] = $where_config["join_with"];
                $loop++;
            }
            $where_config["join_with"] = $new_array;
        }
        if (count($where_config["join_with"]) != (count($where_config["types"]) - 1)) {
            $failed_on = "where_config join_with count error";
            return false;
        }
        return !$this->buildWhere($sql, $bind_text, $bind_args, $where_config, $main_table_id, $auto_ids);
    }
    /**
     * autoIdWhere
     * attachs the table name before the fieldname
     * if auto_ids is set to true
     * used when we are loading from multiple tables at once
     * like a crazy person.
     * returns table.field or field
     */
    protected function autoIdWhere(string $field, string $main_table_id, bool $auto_ids): string
    {
        if ($auto_ids == true) {
            if (strpos($field, ".") === false) {
                return $main_table_id . "." . $field;
            }
        }
        return $field;
    }
    /**
     * buildWhereCaseIs
     * used for IS and IS NOT match cases
     * if the match is not one of these nothing happens.
     */
    protected function buildWhereCaseIs(string &$current_where_code, string $field, string $match): void
    {
        if (in_array($match, ["IS", "IS NOT"]) == false) {
            return;
        }
        $current_where_code .= "" . $field . " " . $match . " NULL ";
    }
    /**
     * buildWhereCaseLike
     * used for LIKE where cases
     * using the magic of string replacement
     * accepts % LIKE, LIKE %, % LIKE % and LIKE
     * any other match type is skipped.
     */
    protected function buildWhereCaseLike(
        string &$current_where_code,
        string $field,
        string $match,
        string &$bind_text,
        array &$bind_args,
        $value,
        string $type
    ): void {
        if (in_array($match, ["LIKE", "% LIKE", "LIKE %","% LIKE %"]) == false) {
            return;
        }
        $value = strtr(strtr($match, " ", ""), "LIKE", $value);
        $current_where_code .= "" . $field . " " . $match . " ?";
        $bind_text .= $type;
        $bind_args[] = $value;
    }
    /**
     * buildWhereCaseIn
     * used for IN and NOT IN where cases
     * any other match type is skipped.
     */
    protected function buildWhereCaseIn(
        string &$current_where_code,
        string $field,
        string $match,
        string &$bind_text,
        array &$bind_args,
        $value,
        string $type,
        string &$sql
    ): void {
        if (in_array($match, ["IN","NOT IN"]) == false) {
            return;
        }
        if (is_array($value) == false) {
            $sql = "empty_in_array";
            return;
        }
        if (count($value) > 0) {
            $sql = "empty_in_array";
            return;
        }
        $current_where_code .=  $field . " " . $match . " (";
        $addon2 = "";
        foreach ($value as $entry) {
            $current_where_code .= $addon2 . " ? ";
            $addon2 = ", ";
            $bind_text .= $type;
            $bind_args[] = $entry;
        }
        $current_where_code .= ") ";
    }
    /**
     * whereJoinProcessor
     * using the grouping options
     * splits up the where fields
    */
    protected function whereJoinProcessor(string &$current_where_code, int &$open_groups, string $join_with): void
    {
        $open_only = ["AND(", "OR("];
        $close_only = [")AND", ")OR"];
        $close_then_reopen = ["(AND)", "(OR)"];
        $open_group = false;
        $close_group = false;
        if (in_array($join_with, $open_only) == true) {
            $open_group = true;
        } elseif (in_array($join_with, $close_only) == true) {
            $close_group = true;
        } elseif (in_array($join_with, $close_then_reopen) == true) {
            $close_group = true;
            $open_group = true;
        }
        if ($close_group == true) {
            if ($open_groups > 0) {
                $current_where_code .= " ) ";
                $open_groups--;
            }
        }
        if ($open_group == true) {
            $current_where_code .= " ( ";
            $open_groups++;
        }
        $current_where_code .= " ";
        $current_where_code .= strtr($join_with, ["(" => "", ")" => ""]);
        $current_where_code .= " ";
    }
    /**
     * whereCaseProcessor
     * redirects the builder to the correct
     * where case.
     */
    protected function whereCaseProcessor(
        string &$current_where_code,
        string $field,
        string $match,
        string &$bind_text,
        array &$bind_args,
        string $main_table_id,
        $value,
        string $type,
        string &$sql,
        bool $auto_ids
    ): void {
        $field = $this->autoIdWhere($field, $main_table_id, $auto_ids);
        if (in_array($match, ["IS","IS NOT"]) == true) {
            $this->buildWhereCaseIs($current_where_code, $field, $match);
        } elseif (in_array($match, ["% LIKE","LIKE %","% LIKE %"]) == true) {
            $this->buildWhereCaseLike($current_where_code, $field, $match, $bind_text, $bind_args, $value, $type);
        } elseif (in_array($match, ["IN","NOT IN"]) == true) {
            $this->buildWhereCaseIn($current_where_code, $field, $match, $bind_text, $bind_args, $value, $type, $sql);
        } else {
            $current_where_code .= "" . $field . " " . $match . " ?";
            $bind_text .= $type;
            $bind_args[] = $value;
        }
    }
    /**
     * buildWhere
     * oh lord, he coming,
     * builds the where statement using the
     * settings past to it.
     */
    protected function buildWhere(
        string &$sql,
        string &$bind_text,
        array &$bind_args,
        array $where_config,
        string $main_table_id = "",
        bool $auto_ids = false
    ): void {
        $loop = 0;
        $current_where_code = "";
        $open_groups = 0;
        while ($loop < count($where_config["fields"])) {
            $match = $where_config["matches"][$loop];
            if ($match == "NULL") {
                $match = null;
            }
            $type = $where_config["types"][$loop];
            $value = $where_config["values"][$loop];
            $field = $where_config["fields"][$loop];
            $this->whereCaseProcessor(
                $current_where_code,
                $field,
                $match,
                $bind_text,
                $bind_args,
                $main_table_id,
                $value,
                $type,
                $sql,
                $auto_ids
            );
            if ($sql == "empty_in_array") {
                break;
            }
            $join_with = null;
            if ($loop < count($where_config["join_with"])) {
                $join_with = $where_config["join_with"][$loop];
            }
            if ($join_with != null) {
                $this->whereJoinProcessor($current_where_code, $open_groups, $join_with);
            }
            $loop++;
        }
        if ($sql != "empty_in_array") {
            while ($open_groups > 0) {
                $current_where_code .= " ) ";
                $open_groups--;
            }
            if ($current_where_code != "") {
                $sql .= " WHERE " . $current_where_code;
            }
        }
    }
}
