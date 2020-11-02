<?php

namespace YAPF\MySQLi;

abstract class MysqliFunctions extends MysqliCore
{
    /**
     * prepairBindExecute
     * shared by Add,Remove,Select and Update
     * this runs the code on the database after all needed
     * checks have finished.
     * @return mixed[] [status => bool, message => string, "stm" => false|statement object]
     */
    protected function SQLprepairBindExecute(string &$sql, array &$bind_args, string &$bind_text): array
    {
        $this->lastSql = $sql;
        $stmt = $this->sqlConnection->prepare($sql);
        if ($stmt == false) {
            $error_msg = "unable to prepair: " . $sql . " because " . $this->sqlConnection->error;
            return ["status" => false, "message" => $error_msg, "stmt" => false];
        }
        $bind_ok = true;
        if (count($bind_args) > 0) {
            $bind_ok = mysqli_stmt_bind_param($stmt, $bind_text, ...$bind_args);
        }
        if ($bind_ok == false) {
            $error_msg = "unable to bind because: " . $stmt->error;
            $stmt->close();
            return ["status" => false, "message" => $error_msg, "stmt" => false];
        }
        $execute_result = $stmt->execute();
        if ($execute_result == false) {
            $error_msg = "unable to execute because: " . $stmt->error;
            $stmt->close();
            return ["status" => false, "message" => $error_msg, "stmt" => false];
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
     * sqlStartTest
     * Attempts to create a mysql connection
     * and returns the result of this test
     * - if stop is set to false the connection is kept
     * open, good if you need to hop to other databases.
     */
    public function sqlStartTest(string $user, string $pass, string $db, bool $stop = false, ?string $host = null): bool
    {
        $this->sqlStop();
        if ($host == null) {
            $host = $this->dbHost;
        }
        $this->sqlConnection = mysqli_connect($host, $user, $pass, $db);
        $error_code = mysqli_connect_errno($this->sqlConnection);
        if ($error_code) {
            $error_msg = "Attempting custom sql connection failed with code: " . $error_code;
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            return false;
        }
        if ($stop == true) {
            $this->sqlStop();
        }
        return true;
    }
    /**
     * sqlSave
     * if there has been no errors and its marked as need to save
     * close the transaction applying the changes to the database
     * for reals.
     * the stop flag is set to true, so this would close
     * the SQL connection, if you want todo more changes
     * then set stop to false.
     */
    public function sqlSave(bool $stop = true): bool
    {
        $commit_status = false;
        if ((!$this->hadErrors) && ($this->needToSave)) {
            $commit_status = $this->sqlConnection->commit();
        }
        if ($commit_status == false) {
            $error_msg = "SQL error [Commit]: " . $this->sqlConnection->error;
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
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
        $this->sqlConnection->rollback();
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
            $error_msg = "DB config is not vaild to start!";
            $this->addError(__FILE__, __FUNCTION__, "DB config password is null!");
            $this->dbPass = "";
        }
        $this->sqlConnection = @mysqli_connect($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);
        $error_code = mysqli_connect_errno($this->sqlConnection);
        if ($error_code) {
            $error_msg = "Attempting sql connection failed with code: " . $error_code;
            $this->addError(__FILE__, __FUNCTION__, $error_msg);
            return false;
        }
        $this->sqlConnection->autocommit(false); // disable auto commit.
        return true;
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
