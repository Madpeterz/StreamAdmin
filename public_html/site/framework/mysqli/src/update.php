<?php
abstract class mysqli_update extends mysqli_add
{
    public function updateV2(string $table,array $update_config,array $where_config,int $expected_changes=1)
    {
        /*
            $where_config: see selectV2.readme
            $update_config = array(
                "fields" => array(),  // a field
                "values" => array(),  // value or null
                "types" => array(),   // s,i,d
            );
        */
        if($this->sqlStart())
        {
            if($table != null)
            {
                $failed = true;
                $failed_on = "";
                $update_config_ok = false;
                if(count($update_config["fields"]) == count($update_config["values"]))
                {
                    if(count($update_config["types"]) == count($update_config["values"]))
                    {
                        if(count($update_config["types"]) > 0)
                        {
                            $failed = false;
                        }
                        else $failed_on = "Update types must have a count of 1 or higher!";
                    }
                    else $failed_on = "Update types does not match update values count!";
                }
                else $failed_on = "Update fields does not match update values count!";

                if($failed == false)
                {
                    $bind_text = "";
                    $bind_args = array();
                    // sql
                    $sql = "UPDATE " . $table . " ";
                    // set to
                    $loop = 0;
                    $addon = "";
                    while($loop < count($update_config["values"]))
                    {
                        if($loop == 0) $sql .= "SET ";
                        $sql .= $addon;
                        $sql .= $update_config["fields"][$loop]."= ";
                        if(($update_config["values"][$loop] == null) && ($update_config["values"][$loop] !== 0)) $sql .= " NULL";
                        else
                        {
                            $sql .= "?";
                            $bind_text .= $update_config["types"][$loop];
                            $bind_args[] = $update_config["values"][$loop];
                        }
                        $addon = ", ";
                        $loop++;
                    }
                    // where fields
                    if($failed == false)
                    {
                        if(is_array($where_config) == true)
                        {
                            $failed = $this->process_where($sql,$where_config,$bind_text,$bind_args,$failed_on,"",false);
                        }
                    }
                    if($failed == false)
                    {
                        if($stmt = $this->sqlConnection->prepare($sql))
                        {
                            $bind_ok = true;
                            if(count($bind_args) > 0)
                            {
                                $bind_ok = mysqli_stmt_bind_param($stmt, $bind_text, ...$bind_args);
                            }
                            if($bind_ok == true)
                            {
                                if($stmt->execute() == true)
                                {
                                    $changes = mysqli_stmt_affected_rows($stmt);
    								$stmt->close();
                                    if($changes == $expected_changes)
                                    {
                                        $this->needToSave = true;
                                        return array("status"=>true,"changes" => $changes, "message"=>"update ok");
                                    }
                                    else
                                    {
                                        $this->flagError();
                                        return $this->failure("Unexpeected number of changes wanted ".$expected_changes." but got:".$changes);
                                    }
                                }
                                else
                                {
                                    $stmt->close();
                                    $this->flagError();
                                    return $this->failure("Failed to execute: [".$sql." ".implode(",",$bind_args)."]");
                                }
                            }
                            else return $this->failure("Failed to bind");
                        }
                        else return $this->failure("Failed to prepair");
                    }
                    else return $this->failure("Failed: ".$failed_on."");
                }
                else return $this->failure("Failed: ".$failed_on."");
            }
            else return $this->failure("No table selected for update");
        }
        else return $this->failure("Unable to start SQL");
    }
}
?>
