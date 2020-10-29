<?php
abstract class error_logging
{
	protected $myLastError = "";
	protected function addError($file = "", $functionName = "", $additional = "")
	{
		$this->myLastError = "File: ".$file." Function: ".$functionName." info: ".$additional."";
		error_log($this->myLastError);
	}
}
?>
