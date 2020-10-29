<?php
abstract class mysqli_functions extends mysqli_core
{
    public function has_db_config() :bool
    {
        if($this->dbUser != "[DB_USER]") return true;
        else return false;
    }
    public function get_last_error()
    {
        return $this->last_error;
    }
    public function flagError()
    {
        $this->hadErrors = true;
    }
    protected function create_mysql_connection(string $dbusername,string $dbpass,string $dbname,string $remote_host_target)
    {
        if($remote_host_target != "")
        {
            return mysqli_connect($remote_host_target, $dbusername, $dbpass, $dbname);
        }
        return mysqli_connect($this->dbHost, $dbusername, $dbpass, $dbname);
    }
    public function sqlStart_test(string $dbusername,string $dbpass,string $dbname,bool $auto_stop=false,string $remote_host_target="") :bool
	{
		$this->sqlStop();
        $this->sqlConnection = $this->create_mysql_connection($dbusername,$dbpass,$dbname,$remote_host_target);
		$error_code = mysqli_connect_errno($this->sqlConnection);
		if($error_code)
		{
			$this->failure("Attempting custom sql connection failed with code: ".$error_code."");
			return false;
		}
		if($auto_stop == true) $this->sqlStop();
		return true;
	}
    public function sqlSave(bool $stop_after=true) :bool
    {
        $commit_status = false;
        if((!$this->hadErrors) && ($this->needToSave)) $commit_status = $this->sqlConnection->commit();
        if($commit_status == false) $this->last_error = "SQL error [Commit]: ".$this->sqlConnection->error."";
        if($stop_after == true) $this->sqlStop();
        return $commit_status;
    }
    public function sqlRollBack()
    {
        $this->sqlConnection->rollback();
        $this->sqlStop();
    }

    protected function sql_arrayAddEquals(array $input = array()) : array
    {
        $returnArray = array();
        foreach($input as $entry)
        {
            $returnArray[] = array($entry=>"=");
        }
        return $returnArray;
    }
    protected function failure(string $message = "No message given.") : array
    {
        $this->addError(__FILE__,__FUNCTION__,$message);
        $this->last_error = "SQL failure: ".$message;
        return array("status"=>false, "message"=>$message);
    }
	public function sqlStart() :bool
	{
		if($this->sqlConnection == null)
		{
			if($this->has_db_config())
			{
                if($this->dbPass === null)
                {
                    error_log("Warning: SQL connection without password is bad");
                    $this->dbPass = "";
                }
                $this->sqlConnection = @mysqli_connect($this->dbHost, $this->dbUser, $this->dbPass, $this->dbName);
                $error_code = mysqli_connect_errno($this->sqlConnection);
                if($error_code)
                {
                    $this->failure("Attempting sql connection failed with code: ".$error_code."");
                    return false;
                }
				$this->sqlConnection->autocommit(false);
                return true;
			}
            return false;
		}
		return true;
	}
	protected function sqlStop() : bool
	{
        $stopped = false;
		if($this->sqlConnection != null)
		{
            $stopped = true;
			$this->sqlConnection->close();
		}
		$this->sqlConnection = null;
		$this->hadErrors = false;
		$this->needToSave = false;
        return $stopped;
	}

}
?>
