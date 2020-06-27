<?php
abstract class mysqli_add extends mysqli_binds
{
    public function addV2($config=array())
    {
        /*
            $config = array(
                "table" => "",
                "fields" => array(),
                "values" => array(),
                "types" => array(),  // s,i,d ect
            );
        */
        if($this->sqlStart())
        {
            $require_indexs = array("table","fields","values","types");
            $all_ok = true;
            $missing_index = "";
            foreach($require_indexs as $index)
            {
                if(array_key_exists($index,$config) == false)
                {
                    $all_ok = false;
                    $missing_index = $index;
                    break;
                }
            }
            if($all_ok == true)
            {
                if(count($config["fields"]) == count($config["values"]))
                {
                    if(count($config["types"]) == count($config["values"]))
                    {
                        $sql = "INSERT INTO " . $config["table"] . " (" . implode(', ', $config["fields"]) . ") VALUES (";
                        $loop = 0;
                        $bind_text = "";
                        $bind_args = array();
                        $addon = "";
                        while($loop < count($config["values"]))
                        {
                            $sql .= $addon;
                            if(($config["values"][$loop] == null) && ($config["values"][$loop] !== 0)) $sql .= " NULL";
                            else
                            {
                                $sql .= "?";
                                $bind_text .= $config["types"][$loop];
                                $bind_args[] = $config["values"][$loop];
                            }
                            $addon = " , ";
                            $loop++;
                        }
                        $sql .= " )";
                        $this->lastSql = $sql;
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
                                    $newID = mysqli_insert_id($this->sqlConnection);
                                    $rowsAdded = mysqli_affected_rows($this->sqlConnection);
                                    if($rowsAdded > 0) $this->needToSave = true;
                                    $stmt->close();
                                    return array("status"=>true, "message"=>"ok","newID"=>$newID, "rowsAdded"=>$rowsAdded);
                                }
                                else
                                {
                                    $err_msg = $stmt->error;
                                    $stmt->close();
                                    return $this->failure("unable to execute: ".$err_msg."");
                                }
                            }
                            else
                            {
                                $stmt->close();
                                return $this->failure("unable to bind");
                            }
                        }
                        else return $this->failure("unable to prepair");
                    }
                    else return $this->failure("types and values counts do not match!");
                }
                else return $this->failure("fields and values counts do not match!");
            }
            else return $this->failure("required index ".$missing_index." is missing!");
        }
        else return $this->failure("Unable to start SQL");
    }
}
?>
