<?php
$sql_debug = false;
abstract class mysqli_core extends db
{
	protected $sqlConnection = null;
	protected $hadErrors = false;
	protected $needToSave = false;
	public $lastSql;
	protected $track_table_select_access = false;
	protected $track_select_from_tables = array();
	protected $last_error = "No error logged";

	function __destruct()
	{
		if($this->sqlConnection != null)
		{
			if(($this->hadErrors == false) && ($this->needToSave == true)) $this->sqlConnection->commit();
			else $this->sqlConnection->rollback();
			$this->sqlStop();
		}
	}
	public function get_last_sql()
	{
		return $this->lastSql;
	}
    public function sqlStart() : bool
    {
        return false;
    }
    protected function sqlStop() : bool
    {
        return false;
    }

	// runs a saved file
	public function RawSQL(string $path_to_file) : array
	{
		if($this->sqlStart())
		{
			if(file_exists($path_to_file) == true)
			{
				$commands = array();
				$lines = file($path_to_file);
				if($lines > 0)
				{
					// Loop through our array, show HTML source as HTML source; and line numbers too.
					$current_command = "";
					foreach ($lines as $line_num => $line)
					{
						$trimmed = trim($line);
						if(strlen($trimmed) > 0)
						{
							// not empty
							if(stripos($trimmed,"--") === false)
							{
								// not a multi line comment
								if(stripos($trimmed,"/*!") === false)
								{
									$current_command = "".$current_command." ".$trimmed."";
									if (substr($trimmed, -1) == ';')
									{
										$commands[] = $current_command;
										$current_command = "";
									}
								}
							}
						}
					}
					if($current_command != "")
					{
						echo "Warning: raw sql has no ending ;";
						$commands[] = $current_command;
					}
					$had_error = false;
					$commands_run = 0;
					if(count($commands) > 0)
					{
						foreach($commands as $command)
						{
							if ($this->sqlConnection->real_query($command) == true)
							{
								$commands_run++;
							}
							else
							{
								$had_error = true;
								break;
							}
						}
						if($commands_run > 0)
						{
							if($had_error == false)
							{
								$this->needToSave = true;
								return array("status"=>true,"message"=>"".$commands_run." commands run");
							}
							else return $this->failure("raw sql failed in some way <br/>\n".$this->sqlConnection->error);
						}
						else return $this->failure("Unable to issue commands<br/>\n".$this->sqlConnection->error);
					}
					else return $this->failure("no commands found in file");
				}
				else return $this->failure("unable to read file:".$path_to_file."");
			}
			else return $this->failure("Unable to see file to read");
		}
		else return $this->failure("unable to start SQL driver");
	}

}
?>
