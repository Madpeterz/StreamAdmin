<?php

namespace YAPF\MySQLi;

use Exception;

abstract class MysqliFunctions extends MysqliCore
{
    public bool $fullSqlErrors = false;

    /**
     * convertIfBool
     * takes a input and if its a bool converts it to a int
     * otherwise returns input
     * @return mixed
     */
    public function convertIfBool($input)
    {
        if ($input === false) {
            return 0;
        } elseif ($input === true) {
            return 1;
        }
        return $input;
    }

    /**
     * RawSQL
     * runs a stored sql file from disk
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function rawSQL(string $path_to_file): array
    {
        if (file_exists($path_to_file) == false) {
            return $this->addError(__FILE__, __FUNCTION__, "Unable to see file to read");
        }
        if ($this->sqlStart() == false) {
            return $this->addError(__FILE__, __FUNCTION__, "Unable to start SQL");
        }

        $commands = [];
        $lines = file($path_to_file);
        if (count($lines) == 0) {
            return $this->addError(__FILE__, __FUNCTION__, "File is empty");
        }

        $current_command = "";
        foreach ($lines as $line_num => $line) {
            $trimmed = trim($line);
            $whyskip = "";
            if (
                (strlen($trimmed) > 0) // not empty
                && (stripos($trimmed, "--")  === false) // not a SQL comment
                && (stripos($trimmed, "/*") === false) // not a magic mysql command
                && (stripos($trimmed, "*") !== 0) // part of multiline comment
                && (stripos($trimmed, "*\\") !== 0) // ending of multiline comment
            ) {
                $current_command .= " " . $trimmed;
                if (substr($trimmed, -1) == ';') {
                    $commands[] = $current_command;
                    $current_command = "";
                }
            }
        }

        $current_command = trim($current_command);
        if ($current_command != "") {
            $this->addError($path_to_file, __FUNCTION__, "Warning: raw sql has no ending ;");
            $commands[] = $current_command . ";";
        }
        if (count($commands) == 0) {
            return $this->addError(__FILE__, __FUNCTION__, "No commands processed from file");
        }

        $had_error = false;
        $commands_run = 0;
        foreach ($commands as $command) {
            $this->lastSql = $command;
            if ($this->sqlConnection->real_query($command) == true) {
                    $commands_run++;
            } else {
                $had_error = true;
                break;
            }
        }

        if ($had_error == true) {
            $error_msg = "raw sql failed in some way maybe error message can help: \n";
            $error_msg .= $this->sqlConnection->error;
            return $this->addError(__FILE__, __FUNCTION__, $error_msg);
        }

        $this->needToSave = true;
        return ["status" => true,"message" => "" . $commands_run . " commands run"];
    }
    protected function selectBuildJoins(?array $join_tables, string &$sql, bool &$failed, string &$failed_on): void
    {
        if ($join_tables == null) {
            return;
        }
        $all_found = true;
        $counts_match = true;
        $required_keys = ["tables","types","onfield_left","onfield_match","onfield_right"];
        $missing_join_key = "";
        foreach ($required_keys as $key) {
            if (array_key_exists($key, $join_tables) == false) {
                $missing_join_key = $key;
                $all_found = false;
                break;
            }
        }
        if ($all_found == false) {
            $failed_on = "Join tables config missing key: " . $missing_join_key;
            return;
        }
        $last_key = "";
        foreach ($required_keys as $key) {
            if ($last_key != "") {
                if (count($join_tables[$key]) != count($join_tables[$last_key])) {
                    $failed_on = "counts match error " . $key . " <=> " . $last_key;
                    $counts_match = false;
                    break;
                }
            }
            $last_key  = $key;
        }
        if ($counts_match == false) {
            $failed = true;
            return;
        }
        $failed = false;
        $loop = 0;
        while ($loop < count($join_tables["tables"])) {
            $sql .= " " . $join_tables["types"][$loop] . " " . $join_tables["tables"][$loop] . "";
            if ($join_tables["onfield_left"][$loop] != "") {
                $sql .= " ON " . $join_tables["onfield_left"][$loop] . " ";
                $sql .= $join_tables["onfield_match"][$loop] . " " . $join_tables["onfield_right"][$loop] . "";
            }
            $loop++;
        }
    }
    protected function selectBuildTableIds(
        ?array $join_tables,
        string &$main_table_id,
        bool &$auto_ids,
        bool &$clean_ids
    ): void {
        if (is_array($join_tables) == true) {
            $main_table_id = "mtb";
            $clean_ids = true;
            if (array_key_exists("main_table_id", $join_tables) == true) {
                $main_table_id = $join_tables["main_table_id"];
                $auto_ids = false;
            }
            if (array_key_exists("cleanids", $join_tables) == true) {
                $clean_ids = $join_tables["cleanids"];
                $auto_ids = false;
            }
        }
    }

    protected function selectBuildFields(
        string &$sql,
        array $basic_config
    ): void {
        if (array_key_exists("fields", $basic_config) == false) {
            $sql .= " *";
        } else {
            $sql .= " " . implode(", ", $basic_config["fields"]);
        }
    }
    /**
     * prepairBindExecute
     * shared by Add,Remove,Select and Update
     * this runs the code on the database after all needed
     * checks have finished.
     * @return mixed[] [status => bool, message => string, "stm" => false|statement object]
     */
    protected function SQLprepairBindExecute(
        array $error_addon,
        string &$sql,
        array &$bind_args,
        string &$bind_text
    ): array {
        $sql = strtr($sql, ["  " => " "]);
        $sql = trim($sql);
        $this->lastSql = $sql;
        $stmt = $this->sqlConnection->prepare($sql);
        if ($stmt == false) {
            $error_msg = "unable to prepair: " . $this->sqlConnection->error;
            if ($this->ExpectedErrorFlag == true) {
                return ["status" => false, "message" => $error_msg, "stmt" => null];
            }
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        if (count($bind_args) > 0) {
            try {
                mysqli_stmt_bind_param($stmt, $bind_text, ...$bind_args);
            } catch (Exception $e) {
                $stmt->close();
                $error_msg = "Unable to bind to statement";
                if ($this->fullSqlErrors == true) {
                    $error_msg .= ": ";
                    $error_msg .= $e->getMessage();
                }
                if ($this->ExpectedErrorFlag == true) {
                    return ["status" => false, "message" => $error_msg, "stmt" => null];
                }
                return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
            }
        }

        $execute_result = $stmt->execute();
        if ($execute_result == false) {
            $error_msg = "unable to execute because: " . $stmt->error;
            $stmt->close();
            if ($this->ExpectedErrorFlag == true) {
                return ["status" => false, "message" => $error_msg, "stmt" => null];
            }
            return $this->addError(__FILE__, __FUNCTION__, $error_msg, $error_addon);
        }
        return ["status" => true, "message" => "ok", "stmt" => $stmt];
    }
    /**
     * hasDbConfig
     * Checks if the set database user is in the disallowed list
     */
    public function hasDbConfig(): bool
    {
        $disallowed_users = ["[DB_USER]", "", " "];
        return !in_array($this->dbUser, $disallowed_users);
    }
    /**
     * flagError
     * sets the hadErrors flag to true
     */
    public function flagError(): void
    {
        $this->hadErrors = true;
    }
    /**
     * sqlStartConnection
     * Attempts to create a mysql connection
     * and returns the result of this test
     * - if stop is set to false the connection is kept
     * open, good if you need to hop to other databases.
     */
    public function sqlStartConnection(
        string $user,
        string $pass,
        string $db,
        bool $stop = false,
        ?string $host = null,
        int $timeout = 3
    ): bool {
        $this->sqlStop();
        $this->sqlConnection = mysqli_init();
        mysqli_options($this->sqlConnection, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
        if ($host == null) {
            $host = $this->dbHost;
        }
        try {
            $status = mysqli_real_connect($this->sqlConnection, $host, $user, $pass, $db);
            if ($status == true) {
                if ($stop == true) {
                    $this->sqlStop();
                }
            }
            return $status;
        } catch (Exception $e) {
            if ($this->fullSqlErrors == true) {
                $this->addError(__FILE__, __FUNCTION__, "SQL connection error: " . $e->getMessage());
            } else {
                $this->addError(__FILE__, __FUNCTION__, "Connect attempt died in a fire");
            }
            return false;
        }
    }
    /**
     * sqlSave
     * if there has been no errors and its marked as need to save
     * close the transaction applying the changes to the database
     * for reals.
     * the stop flag is set to true, so this would close
     * the SQL connection, if you want todo more changes
     * then set stop to false.
     * returns true if saved (or no changes), false if rollback
     */
    public function sqlSave(bool $stop = true): bool
    {
        $commit_status = false;
        if (($this->hadErrors == false) && ($this->needToSave == true)) {
            $commit_status = $this->sqlConnection->commit();
            if ($commit_status == false) {
                $error_msg = "SQL error [Commit]: " . $this->sqlConnection->error;
                $this->addError(__FILE__, __FUNCTION__, $error_msg);
            }
        } elseif (($this->hadErrors == true) && ($this->needToSave == true)) {
            $this->sqlRollBack();
        } else {
            $this->myLastErrorBasic = "No changes made";
            $commit_status = true; // no changes (save ok)
        }
        if ($stop == true) {
            $this->sqlStop();
        }
        return $commit_status;
    }
    /**
     * sqlRollBack
     * rolls back the changes that have been made to the database
     * from the last Save or new connection.
     * also closes the SQL connection as we should stop
     * what we are doing if there needs to be a rollback.
     */
    public function sqlRollBack(): void
    {
        if ($this->sqlConnection != null) {
            $this->sqlConnection->rollback();
        }
        $this->sqlStop();
    }
    /**
     * sqlRollBack
     * rolls back the changes that have been made to the database
     * from the last Save or new connection.
     * also closes the SQL connection as we should stop
     * what we are doing if there needs to be a rollback.
     */
    public function sqlStart(): bool
    {
        if ($this->sqlConnection != null) {
            return true; // connection is already open!
        }
        if ($this->hasDbConfig() == false) {
            $error_msg = "DB config is not vaild to start!";
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            return false;
        }
        if ($this->dbPass === null) {
            $error_msg = "DB config password is null!";
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            $this->dbPass = "";
        }
        $status = $this->sqlStartConnection($this->dbUser, $this->dbPass, $this->dbName, false, $this->dbHost, 5);
        if ($status == true) {
            $this->sqlConnection->autocommit(false); // disable auto commit.
        }
        return $status;
    }
    /**
     * sqlStop
     * stops the open sql connection
     * without saving anything and resets
     * the flags.
     * returns true if a currently open connection
     * was closed.
     */
    protected function sqlStop(): bool
    {
        $this->hadErrors = false;
        $this->needToSave = false;
        if ($this->sqlConnection != null) {
            $this->sqlConnection->close();
            $this->sqlConnection = null;
            return true;
        }
        return false;
    }
}
