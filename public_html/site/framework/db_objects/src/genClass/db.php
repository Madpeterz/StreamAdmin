<?php
abstract class genClass_db extends genClass_load
{
    public function remove_me() :array
    {
        if($this->disabled == false)
        {
            if($this->get_id() > 0)
            {
                $wherefields = array(array("id" => "="));
                $wherevalues = array(array($this->get_id() => "i"));
                $remove_status = $this->sql->remove($this->get_table(),$wherefields,$wherevalues);
                if($remove_status["status"] == true)
                {
                    $this->dataset["id"]["value"] = -1;
                    return $remove_status;
                }
                else return $remove_status;
            }
        }
        else return array("status"=>false,"message"=>"this class is disabled.");
    }
    public function create_entry() :array
    {
        if($this->disabled == false)
        {
            if(array_key_exists("id",$this->save_dataset) == true)
            {
                if($this->save_dataset["id"]["value"] == null)
                {
                    $fields = array();
                    $setto = array();
                    foreach($this->dataset as $key => $value)
                    {
                        if(!in_array($key,array("id")))
                        {
                            $value = $this->dataset[$key]["value"];
                            $fields[] = $key;
                            $update_code = "i";
                            if($this->dataset[$key]["type"] == "str") $update_code = "s";
                            else if($this->dataset[$key]["type"] == "float") $update_code = "d";
                            $setto[] = array($value => $update_code);
                        }
                    }
                    if(count($fields) > 0)
                    {
    					$add_status = $this->sql->add($this->get_table(), $fields, $setto);
    					if($add_status["status"] == true)
    					{
    						$this->dataset["id"]["value"] = $add_status["newID"];
    						$this->save_dataset["id"]["value"] = $add_status["newID"];
    					}
                        return $add_status;
                    }
                    else
                    {
                        return array("status"=>false, "message"=>"No fields set to create with!");
                    }
                }
                else
                {
                    return array("status"=>false, "message"=>"attempting to create a object with a set id, this is not allowed!");
                }
            }
            else
            {
                return array("status"=>false, "message"=>"All objects must have a id field!");
            }
        }
        else return array("status"=>false,"message"=>"this class is disabled.");
    }
    public function save_changes() :array
    {
        if($this->disabled == false)
        {
    		if (array_key_exists("id", $this->save_dataset))
    		{
    			if ($this->save_dataset["id"]["value"] > 0)
    			{
    				$update_fields = array();
    				$update_values = array();
    				$had_error = false;
    				$error_msg = "";
    				foreach($this->save_dataset as $key => $value)
    				{
    					if ($key != "id")
    					{
    						if (array_key_exists($key, $this->save_dataset))
    						{
    							if (array_key_exists("value", $this->dataset[$key]))
    							{
    								if ($this->dataset[$key]["value"] != $this->save_dataset[$key]["value"])
    								{
    								    $update_fields[] = $key;
										$update_code = "i";
                                        $value = $this->dataset[$key]["value"];
										if ($this->dataset[$key]["type"] == "str") $update_code = "s";
                                        else if ($this->dataset[$key]["type"] == "float") $update_code = "d";
										$update_values[] = array(
											$value => $update_code
										);
    								}
    							}
    							else
    							{
    								$had_error = true;
    								$error_msg = "attempting to read value for key " . $key . " from dataset has failed!";
    								break;
    							}
    						}
    						else
    						{
    							$had_error = true;
    							$error_msg = "Attempting to set field: " . $key . " but this is not supported!";
    							break;
    						}
    					}
    				}

    				if ($had_error == false)
    				{
    					$wherefields = array(
    						array("id" => "=")
    					);
    					$wherevalues = array(
    						array($this->save_dataset["id"]["value"] => "i")
    					);
    					if(count($update_fields) > 0)
    					{
    						return $this->sql->update($this->get_table() , $update_fields, $update_values, $wherefields, $wherevalues);
    					}
    					else
    					{
    						return array(
    							"status" => true,
    							"changes" => 0,
    							"message" => "No changes made"
    						);
    					}
    				}
    				else
    				{
    					return array(
    						"status" => false,
    						"changes" => 0,
    						"message" => "request rejected: " . $error_msg
    					);
    				}
    			}
    			else return array(
    				"status" => false,
    				"changes" => 0,
    				"message" => "Unable to save changes on this object because the id in memory is not vaild!"
    			);
    		}
    		else return array(
    			"status" => false,
    			"changes" => 0,
    			"message" => "Unable to save changes on this object because no vaild dataset was found! did you load first?"
    		);
        }
        else return array("status"=>false,"changes" => 0,"message"=>"this class is disabled.");
    }
}
?>
