<?php
abstract class mysqli_binds extends mysqli_functions
{
    protected function build_select_orderby(string &$sql,array $order_config,string $main_table_id="",bool $auto_ids=false)
    {
        if(array_key_exists("ordering_enabled",$order_config) == true)
        {
            if(array_key_exists("order_field",$order_config) == false)
            {
                $order_config["order_field"] = "id";
                if($auto_ids == true)
                {
                    if(strpos($order_config["order_field"],".") === false)
                    {
                        $order_config["order_field"] = "".$main_table_id.".".$order_config["order_field"]."";
                    }
                }
                $order_config["order_dir"] = "DESC";
                $order_config["ordering_enabled"] = true;
            }
            if($order_config["ordering_enabled"] == true)
            {
                if(array_key_exists("as_string",$order_config) == true)
                {
                    $sql .= " ORDER BY ".$order_config["as_string"]." ";
                }
                else
                {
                    $sql .= " ORDER BY ".$order_config["order_field"]." ".$order_config["order_dir"]." ";
                }
            }
        }
    }
    protected function build_select_option(string &$sql,array $options)
    {
        if(array_key_exists("max_entrys",$options) == true)
        {
            if(array_key_exists("page_number",$options) == true)
            {
                if($options["page_number"] > 0) $sql .= " LIMIT ".($options["page_number"]*$options["max_entrys"]).",".$options["max_entrys"]." ";
                else if($options["max_entrys"] > 0) $sql .= " LIMIT ".$options["max_entrys"]." ";
            }
            else
            {
                if($options["max_entrys"] > 0) $sql .= " LIMIT ".$options["max_entrys"]." ";
            }
        }
    }
    protected function process_where(string &$sql,array $where_config,string &$bind_text,array &$bind_args,string &$failed_on,string $main_table_id="",bool $auto_ids=false) :bool
    {
        $failed = true;
        if($where_config !== null)
        {
            if(is_array($where_config) == true)
            {
                $failed = false;
                $missing_keys_text = "";

                $check_keys = array("fields","values","types","matches");
                $missing_keys = array();
                foreach($check_keys as $test_key)
                {
                    if(array_key_exists($test_key,$where_config) == false)
                    {
                        $missing_keys[] = $test_key;
                    }
                }
                if(count($missing_keys) > 0)
                {
                    $failed = true;
                    $missing_keys_text = " ~ ".implode(",",$missing_keys);
                }
                if(array_key_exists("join_with",$where_config) == false) $where_config["join_with"] = "AND";
                if($failed == false)
                {
                    $failed = true;
                    if(count($where_config["fields"]) == count($where_config["values"]))
                    {
                        if(count($where_config["values"]) == count($where_config["types"]))
                        {
                            if(count($where_config["types"]) == count($where_config["matches"]))
                            {
                                if(count($where_config["fields"]) > 0)
                                {
                                    if(is_array($where_config["join_with"]) == false)
                                    {
                                        $new_array = array();
                                        $loop = 1;
                                        while($loop < count($where_config["types"]))
                                        {
                                            $new_array[] = $where_config["join_with"];
                                            $loop++;
                                        }
                                        $where_config["join_with"] = $new_array;
                                    }
                                    if(count($where_config["join_with"]) == (count($where_config["types"])-1))
                                    {
                                        return !$this->build_where($sql,$bind_text,$bind_args,$where_config,$main_table_id,$auto_ids);
                                    }
                                    else $failed_on = "where_config join_with count error";
                                }
                                else
                                {
                                    $failed = false;
                                    $failed_on = "Nothing todo";
                                }
                            }
                            else $failed_on = "where_config count error on types to match";
                        }
                        else $failed_on = "where_config count error on values to types";
                    }
                    else $failed_on = "where_config count error on fields to values";
                }
                else $failed_on = "Required where_config keys missing ".$missing_keys_text."";
            }
            else $failed_on = "No where config this error should not display.";
        }
        else $failed_on = "Where config is null";
        return $failed;
    }
    protected function build_where(string &$sql,string &$bind_text,array &$bind_args,array $where_config,string $main_table_id="",bool $auto_ids=false) :bool
    {
        $loop = 0;
        $current_where_code = "";
        $look_up = array("(AND)" => "AND","(OR)" => "OR","AND(" => "AND","OR(" => "OR",")AND" => "AND",")OR" => "OR");
        $open_only = array("AND(","OR(");
        $close_only = array(")AND",")OR");
        $group_options = array("(AND)","(OR)","AND(","OR(",")AND",")OR");
        $open_groups = 0;
        while($loop < count($where_config["fields"]))
        {
            $match = $where_config["matches"][$loop];
            if($match == "NULL") $match = null;
            $type = $where_config["types"][$loop];
            $value = $where_config["values"][$loop];
            $field = $where_config["fields"][$loop];
            if($auto_ids == true)
            {
                if(strpos($field,".") === false)
                {
                    $field = "".$main_table_id.".".$field."";
                }
            }
            if(in_array($match,array("IS","IS NOT","IN","NOT IN")) == false)
            {
                if(in_array($match,array("% LIKE","LIKE %","% LIKE %")) == true)
                {
                    if($match == "% LIKE") $value = "%".$value."";
                    else if($match == "LIKE %") $value = "".$value."%";
                    else if($match == "% LIKE %") $value = "%".$value."%";
                    $match = "LIKE";
                }
                $current_where_code .= "".$field." ".$match." ?";
                $bind_text .= $type;
                $bind_args[] = $value;
            }
            else
            {
                if(in_array($match,array("IS","IS NOT")) == true)
                {
                    $current_where_code .= "".$field." ".$match." NULL ";
                }
                else
                {
                    if(count($value) > 0)
                    {
                        $current_where_code .= "".$field." ".$match." (";
                        $addon2 = "";
                        foreach($value as $entry)
                        {
                            $current_where_code .= "".$addon2." ? ";
                            $addon2 = ", ";
                            $bind_text .= $type;
                            $bind_args[] = $entry;
                        }
                        $current_where_code .= ") ";
                    }
                    else
                    {
                        $sql = "empty_in_array";
                        break;
                    }
                }
            }
            if($loop < count($where_config["join_with"]))
            {
                if(in_array($where_config["join_with"][$loop],$group_options) == true)
                {
                    if(in_array($where_config["join_with"][$loop],$close_only) == true)
                    {
                        $current_where_code .= " ) ";
                        $open_groups--;
                    }
                    else if(in_array($where_config["join_with"][$loop],$open_only) == false)
                    {
                        if($open_groups > 0)
                        {
                            $open_groups--;
                            $current_where_code .= " ) ";
                        }
                    }
                    $current_where_code .= " ".$look_up[$where_config["join_with"][$loop]]." ";
                    if((in_array($where_config["join_with"][$loop],$open_only) == true) || (in_array($where_config["join_with"][$loop],$close_only) == false))
                    {
                        $current_where_code .= " ( ";
                        $open_groups++;
                    }
                }
                else
                {
                    $current_where_code .= " ".$where_config["join_with"][$loop]." ";
                }
            }
            $loop++;
        }
        if($sql != "empty_in_array")
        {
            while($open_groups > 0)
            {
                $current_where_code .= " ) "; // auto close ^+^
                $open_groups--;
            }
            if($current_where_code != "")
            {
                $sql .= " WHERE ".$current_where_code;
            }
        }
        return true;
    }


}

?>
