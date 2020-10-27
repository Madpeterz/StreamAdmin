<?php
abstract class mysqli_remove extends mysqli_update
{
    public function remove($table, $wherefields = array(), $wherevalues = array(), $joinOption = "AND")
	{
        $this->sqlStart();
		if($table != null)
		{
			if((count($wherefields) > 0) && (count($wherefields) == count($wherevalues)))
			{
				$sql = "DELETE FROM " . $table . "";
			 	$sql = $this->bindParams($sql, $wherefields, "WHERE", $joinOption);
			 	if($stmt = $this->sqlConnection->prepare($sql))
			 	{
			 		$this->sql_bind($stmt, $wherevalues);
			 		if($stmt->execute())
			 		{
			 			$rowsChanged = mysqli_affected_rows($this->sqlConnection);
			 			if($rowsChanged > 0) $this->needToSave = true;
			 			$stmt->close();
			 			return array("status"=>true, "rowsDeleted"=>$rowsChanged);
			 		}
			 		else
			 		{
						$this->last_error = "SQL error [Remove]: ".$stmt->error."";
			 			$this->flagError();
						$lastError = $stmt->error;
			 			$stmt->close();
			 			return $this->failure("No remove for you." . $lastError);
			 		}
			 	}
			 	else return $this->failure("Unable to prepare for remove." . $sql);
			}
			else return $this->failure("Incorrect remove paramaters you smart person!");
		}
		else return $this->failure("Please select a table first");
	}
}
?>
