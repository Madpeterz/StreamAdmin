<?php
abstract class mysqli_select extends mysqli_remove
{
    public function selectV2(array $basic_config,?array $order_config=null,?array $where_config=null,?array $options_config=null,?array $join_tables=null) : array
    {
        if(array_key_exists("table",$basic_config) == true)
        {
            if($this->sqlStart() == true)
            {
                $failed = false;
                $failed_on = "";
                $sql = "SELECT ";
                $main_table_id = "";
                $auto_ids = false;
                $clean_ids = false;
                if(is_array($join_tables) == true)
                {
                    $main_table_id = "mtb";
                    $auto_ids = true;
                    $clean_ids = true;
                    if(array_key_exists("main_table_id",$join_tables) == true) $main_table_id = $join_tables["main_table_id"];
                    if(array_key_exists("autoids",$join_tables) == true) $auto_ids = $join_tables["autoids"];
                    if(array_key_exists("cleanids",$join_tables) == true) $clean_ids = $join_tables["cleanids"];
                }
                if(array_key_exists("fields",$basic_config) == true)
                {
                    if(array_key_exists("field_function",$basic_config) == true)
                    {
                        $loop = 0;
                        $addon = "";
                        foreach($basic_config["fields"] as $field)
                        {
                            $sql .= $addon;
                            if(count($basic_config["field_function"]) > $loop)
                            {
                                $sql .= " ".$basic_config["field_function"][$loop]."( ";
                            }
                            if(($main_table_id != "") && ($auto_ids == true)) $sql .= " ".$main_table_id.".".$field."";
                            else $sql .= " ".$field."";
                            if(count($basic_config["field_function"]) > $loop)
                            {
                                $sql .= " )";
                            }
                            $addon = " , ";
                            $loop++;
                        }
                    }
                    else
                    {
                        if(($main_table_id != "") && ($auto_ids == true)) $sql .= " ".$main_table_id.".".implode(", ".$main_table_id.".",$basic_config["fields"]);
                        else $sql .= " ".implode(", ",$basic_config["fields"]);
                    }
                }
                else
                {
                    $clean_ids = false; // no need to clean
                    if($main_table_id != "") $sql .= " ".$main_table_id.".*";
                    else $sql .= " *";
                }
                $sql .= " FROM ".$basic_config["table"]." ".$main_table_id." ";
                // JOINS HERE
                if($main_table_id != "")
                {
                    /*
                    "types" => array(), // "LEFT JOIN", "JOIN", "RIGHT JOIN" ect
                    "tables" => array(), // "tablename x"   example  people pl
                    "onfield_left" => array(), // "pl.id"
                    "onfield_match" => array(), // "=" "!=" ">" ect
                    "onfield_right" => array(), // "mtb.id"
                    */
                    $failed = true;
                    $all_found = true;
                    $counts_match = true;
                    $required_keys = array("tables","types","onfield_left","onfield_match","onfield_right");
                    foreach($required_keys as $key)
                    {
                        if(array_key_exists("tables",$join_tables) == false)
                        {
                            $all_found = false;
                            break;
                        }
                    }
                    if($all_found == true)
                    {
                        $last_key = "";
                        foreach($required_keys as $key)
                        {
                            if($last_key != "")
                            {
                                if(count($join_tables[$key]) != count($join_tables[$last_key]))
                                {
                                    $counts_match = false;
                                    break;
                                }
                            }
                            $last_key  = $key;
                        }
                    }
                    if(($all_found == true) && ($counts_match == true))
                    {
                        $failed = false;
                        $loop = 0;
                        while($loop < count($join_tables["tables"]))
                        {
                            $sql .= " ".$join_tables["types"][$loop]." ".$join_tables["tables"][$loop]."";
                            $sql .= " ON ".$join_tables["onfield_left"][$loop]." ".$join_tables["onfield_match"][$loop]." ".$join_tables["onfield_right"][$loop]."";
                            $loop++;
                        }
                    }
                }
                // end joins
                $bind_text = "";
                $bind_args = array();
                if($failed == false)
                {
                    if(is_array($where_config) == true)
                    {
                        $failed = $this->process_where($sql,$where_config,$bind_text,$bind_args,$failed_on,$main_table_id,$auto_ids);
                    }
                }
                if($failed == false)
                {
                    if($sql != "empty_in_array")
                    {
                        if(is_array($order_config) == true) $this->build_select_orderby($sql,$order_config,$main_table_id,$auto_ids);
                        if(is_array($options_config) == true) $this->build_select_option($sql,$options_config);
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
                                    $result = $stmt->get_result();
                                    $dataSet = array();
                                    if($clean_ids == false)
                                    {
                                        // yay no cleaning super fast
                                        $dataSet = array();
                                        while($entry = $result->fetch_assoc())
                                        {
                                            $dataSet[] = $entry;
                                        }
                                    }
                                    else
                                    {
                                        // oh god here we go time to clean up.
                                        $loop = 0;
                                        while($entry = $result->fetch_assoc())
                                        {
                                            $cleaned_entry = array();
                                            foreach($entry as $field => $value)
                                            {
                                                $field_name_bits = explode(".",$field);
                                                if(count($field_name_bits) > 1) $cleaned_entry[$field_name_bits[1]] = $value;
                                                else $cleaned_entry[$field] = $value;
                                            }
                                            $dataSet[] = $cleaned_entry;
                                            $loop++;
                                        }
                                    }
                                    return array("status"=>true, "dataSet"=>$dataSet ,"message" => "ok","run_sql"=>$sql);
                                }
                                else return array("status"=>false,"message"=>"Unable to execute");
                            }
                            else return array("status"=>false,"message"=>"Unable to bind: ".$bind_text." ".print_r($bind_args,true)."");
                        }
                        else return array("status"=>false,"message"=>"Unable to prepair: ".$sql);
                    }
                    else return array("status"=>true,"message"=>"no data empty in array.","dataSet"=>array());
                }
                else return array("status"=>false,"message"=>"failed with message:".$failed_on);
             }
             else return array("status"=>false,"message"=>"Unable to connect to database");
        }
        else return array("status"=>false,"message"=>"table is required in basic_config arg");
    }
}
?>
