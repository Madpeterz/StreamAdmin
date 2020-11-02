<?php

namespace YAPF\MySQLi;

use App\Db as Db;

abstract class MysqliCore extends Db
{
    protected $sqlConnection = null;
    protected $hadErrors = false;
    protected $needToSave = false;
    public $lastSql = "";
    protected $track_table_select_access = false;
    protected $track_select_from_tables = [];
    protected $last_error = "No error logged";

    public function __destruct()
    {
        if ($this->sqlConnection != null) {
            if (($this->hadErrors == false) && ($this->needToSave == true)) {
                $this->sqlConnection->commit();
            } else {
                $this->sqlConnection->rollback();
            }
            $this->sqlStop();
        }
    }
    /**
     * getLastSql
     * returns the last SQL statement processed
     * good if you want to check what its doing
     */
    public function getLastSql(): string
    {
        return $this->lastSql;
    }
    /**
     * RawSQL
     * runs a stored sql file from disk
     * @return mixed[] [status =>  bool, message =>  string]
     */
    public function rawSQL(string $path_to_file): array
    {
        if ($this->sqlStart() == false) {
            return $this->addError(__FILE__, __FUNCTION__, "Unable to start SQL");
        }
        if (file_exists($path_to_file) == false) {
            return $this->addError(__FILE__, __FUNCTION__, "Unable to see file to read");
        }

        $commands = [];
        $lines = file($path_to_file);
        if ($lines == 0) {
            return $this->addError(__FILE__, __FUNCTION__, "File is empty");
        }

        $current_command = "";
        foreach ($lines as $line_num => $line) {
            $trimmed = trim($line);
            $whyskip = "";
            if (
                (strlen($trimmed) > 0) // not empty
                && (stripos($trimmed, "--")  === false) // not a SQL comment
                && (stripos($trimmed, "/*!") === false) // not a magic mysql command
            ) {
                $current_command .= " " . $trimmed;
                if (substr($trimmed, -1) == ';') {
                    $commands[] = $current_command;
                    $current_command = "";
                }
            }
        }

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
        if ($commands_run == 0) {
            return $this->addError(__FILE__, __FUNCTION__, "No commands run but no error state I have no idea");
        }

        $this->needToSave = true;
        return ["status" => true,"message" => "" . $commands_run . " commands run"];
    }
}
