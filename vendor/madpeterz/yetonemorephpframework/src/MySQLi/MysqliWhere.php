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
        bool &$failed
    ): bool {
        if ($where_config === null) {
            $failed_on = "note: Where config is null skipping";
            return true;
        } elseif (count($where_config) == 0) {
            $failed_on = "where_config is empty but not null!";
            return false;
        }
        $check_keys = ["fields","values","types","matches"];
        $missing_keys = [];
        foreach ($check_keys as $test_key) {
            if (array_key_exists($test_key, $where_config) == false) {
                $missing_keys[] = $test_key;
            }
        }
        if (count($missing_keys) > 0) {
            $failed_on = "missing where keys:" . implode(",", $missing_keys);
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
            $failed_on = "Note: where config keys are empty inside  skipping";
            return true;
        } elseif (array_key_exists("join_with", $where_config) == false) {
            $where_config["join_with"] = "AND";
        }


        if (is_array($where_config["join_with"]) == false) {
            $new_array = [];
            $loop = 1;
            $total = count($where_config["types"]);
            while ($loop < $total) {
                $new_array[] = $where_config["join_with"];
                $loop++;
            }
            $where_config["join_with"] = $new_array;
        }
        if (count($where_config["join_with"]) != (count($where_config["types"]) - 1)) {
            $failed_on = "where_config join_with count error";
            return false;
        }
        $failed_on = "Passed";
        $this->buildWhere($sql, $bind_text, $bind_args, $where_config, $failed, $failed_on);
        if ($failed == true) {
            return false;
        }
        return true;
    }
    /**
     * buildWhereCaseIs
     * used for IS and IS NOT match cases
     * if the match is not one of these nothing happens.
     */
    protected function buildWhereCaseIs(string &$current_where_code, string $field, string $match): void
    {
        $current_where_code .= "" . $field . " " . $match . " null ";
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
        $adj = str_replace(" ", "", $match);
        $value = str_replace("LIKE", $value, $adj);
        $match = "LIKE";
        $current_where_code .= "" . $field . " " . $match . " ? ";
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
        if (is_array($value) == false) {
            $sql = "empty_in_array";
            return;
        }
        if (count($value) == 0) {
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
     * whereCaseProcessor
     * redirects the builder to the correct
     * where case.
     */
    protected function whereCaseProcessor(
        string &$current_where_code,
        string $field,
        ?string $match,
        string &$bind_text,
        array &$bind_args,
        $value,
        string $type,
        string &$sql,
        bool &$failed,
        string &$failed_on
    ): void {
        $allowed_match_types = [
            "=",
            "<=",
            ">=",
            "!=",
            "<",
            ">",
            "IS",
            "IS NOT",
            "% LIKE",
            "LIKE %",
            "% LIKE %",
            "IN",
            "NOT IN",
        ];
        if (in_array($match, $allowed_match_types) == false) {
            $failed = true;
            $failed_on = "Unsupported where match type!";
            return;
        }
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
    protected function whereJoinBuilder(
        string &$sql,
        string &$bind_text,
        array &$bind_args,
        array $where_config,
        bool &$failed,
        string &$failed_on,
        string &$current_where_code
    ): void {
        $open_groups = 1;
        $current_where_code .= "(";
        $end_group_after = [") AND",") OR"];
        $start_group_before = ["( AND","( OR"];
        $loop = 0;
        $pending_closer = 0;
        while ($loop < count($where_config["fields"])) {
            $this->whereCaseWriter(
                $where_config,
                $loop,
                $current_where_code,
                $bind_text,
                $bind_args,
                $sql,
                $failed,
                $failed_on,
                $open_groups,
                $pending_closer
            );
            if ($failed == true) {
                break;
            }
            if (in_array($where_config["join_with"][$loop], $start_group_before) == true) {
                $open_groups++;
                $current_where_code .= "(";
            }
            if (in_array($where_config["join_with"][$loop], $end_group_after) == true) {
                $pending_closer = 1;
            }
            if ($sql == "empty_in_array") {
                break;
            }
            $loop++;
        }
        while ($open_groups > 0) {
            $current_where_code .= ")";
            $open_groups--;
        }
    }

    protected function helperArrayElementInArray(array $a, array $b): bool
    {
        foreach ($a as $entry) {
            if (in_array($entry, $b) == true) {
                return true;
            }
        }
        return false;
    }

    protected function whereCaseWriter(
        array $where_config,
        int $loop,
        string &$current_where_code,
        string &$bind_text,
        array &$bind_args,
        string &$sql,
        bool &$failed,
        string &$failed_on,
        int &$open_groups,
        int &$pending_closer
    ): void {
        $match = $where_config["matches"][$loop];
        $type = $where_config["types"][$loop];
        $value = $where_config["values"][$loop];
        if ($value === "null") {
            $value = null;
        }
        $field = $where_config["fields"][$loop];
        $this->whereCaseProcessor(
            $current_where_code,
            $field,
            $match,
            $bind_text,
            $bind_args,
            $value,
            $type,
            $sql,
            $failed,
            $failed_on
        );
        if ($failed == true) {
            return;
        }
        if ($pending_closer == 1) {
            $pending_closer = 0;
            $open_groups--;
            $current_where_code .= ")";
        }
        if ($sql != "empty_in_array") {
            if (count($where_config["join_with"]) > $loop) {
                $current_where_code .= " ";
                $current_where_code .= strtr($where_config["join_with"][$loop], ["( " => "",") " => ""]);
                $current_where_code .= " ";
            }
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
        bool &$failed,
        string &$failed_on
    ): void {
        $current_where_code = "";
        $complex_builder_triggers = ["( AND", "( OR",") AND", ") OR"];
        if ($this->helperArrayElementInArray($complex_builder_triggers, $where_config["join_with"]) == true) {
            $this->whereJoinBuilder(
                $sql,
                $bind_text,
                $bind_args,
                $where_config,
                $failed,
                $failed_on,
                $current_where_code
            );
        } else {
            $loop = 0;
            $open_groups = 0;
            $pending_closer = 0;
            while ($loop < count($where_config["fields"])) {
                $this->whereCaseWriter(
                    $where_config,
                    $loop,
                    $current_where_code,
                    $bind_text,
                    $bind_args,
                    $sql,
                    $failed,
                    $failed_on,
                    $open_groups,
                    $pending_closer
                );
                if ($failed == true) {
                    break;
                }
                if ($sql == "empty_in_array") {
                    break;
                }
                $loop++;
            }
        }
        if ($sql != "empty_in_array") {
            if ($current_where_code != "") {
                $current_where_code = trim($current_where_code);
                $sql .= " WHERE " . $current_where_code;
            }
        }
    }
}
