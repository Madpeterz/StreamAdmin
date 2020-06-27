<?php
class mysqli_shims extends mysqli_custom
{
    /*
        theses are the old versions of functions that have been updated to use the new
        V2 method but are left here to maintain compatibity for the next version
        these are due to be removed at a later date so should not be used in updates/new projects.
    */
    public function select(string $table,?array $fields,array $wherefields = array(),array $wherevalues = array(),string $whereJoin = "AND",string $orderBy = "",string $orderDir = "DESC",int $limit = 0,int $page = 0)
    {
        // rebuild into V2
        $config = array();
        $basic_config = array("table"=>$table);
        if($fields !== null)
        {
            if(is_array($fields) == true) $basic_config["fields"] = $fields;
        }
        $config[] = $basic_config;
        $order_enabled = false;
        if($orderBy != "") $order_enabled = true;
        $config[] = array("ordering_enabled"=>$order_enabled,"order_field"=>$orderBy,"order_dir"=>$orderDir);
        $where_fields = array();
        $where_values = array();
        $where_types = array();
        $where_match = array();
        foreach($wherefields as $data => $set)
        {
            foreach($set as $field => $match)
            {
                $where_fields[] = $field;
                $where_match[] = $match;
            }
        }
        foreach($wherevalues as $data => $set)
        {
            foreach($set as $value => $vtype)
            {
                $where_types[] = $vtype;
                if($value === null) $value = "NULL";
                $where_values[] = $value;
            }
        }
        $where_config = array("join_with"=>$whereJoin,"fields"=>$where_fields,"values"=>$where_values,"types"=>$where_types,"matches"=>$where_match);
        $config[] = $where_config;
        $config[] = array("page_number"=>$page,"max_entrys"=>$limit);
        $reply = $this->selectV2(...$config);
        return $reply;
    }
    public function multi_table_group_count(array $tables,string $group_field,array $wherefields = array(),array $wherevalues = array(),string $joinOption = "AND") : array
    {
        // rebuild into V2
        $table_data = array();
        foreach($tables as $table)
        {
            $table_data[$table] = $group_field;
        }
        $where_fields = array();
        $where_values = array();
        $where_types = array();
        $where_match = array();
        foreach($wherefields as $data => $set)
        {
            foreach($set as $field => $match)
            {
                $where_fields[] = $field;
                $where_match[] = $match;
            }
        }
        foreach($wherevalues as $data => $set)
        {
            foreach($set as $value => $vtype)
            {
                $where_types[] = $vtype;
                if($value === null) $value = "NULL";
                $where_values[] = $value;
            }
        }
        $where_config = array("join_with"=>$joinOption,"fields"=>$where_fields,"values"=>$where_values,"types"=>$where_types,"matches"=>$where_match);
        return $this->multi_table_group_count_v2($table_data,$where_config);
    }
    public function add($table, $setFields = array(), $setValues = array())
	{
		if($table != null)
		{
			if(count($setFields) > 0 && count($setFields) == count($setValues))
            {
                // repack for V2
                $config  = array(
                    "table" => $table,
                    "fields" => $setFields,
                    "values" => array(),
                    "types" => array(),
                );
                foreach($setValues as $entry)
                {
                    foreach($entry as $value => $type)
                    {
                        $config["values"][] = $value;
                        $config["types"][] = $type;
                    }
                }
                return $this->addV2($config);
            }
			else return $this->failure("Incorrect add paramaters you fucktard!");
		}
		else return $this->failure("Please select a table first");
    }
    public function update($table, $fields, $setTo, $wherefields = array(), $wherevalues = array(), $whereJoin = "AND",$expected_changes=1)
    {
        // redirect to V2
        if(count($fields) > 0)
        {
            if(count($wherefields) == count($wherevalues))
            {
                if(count($fields) == count($setTo))
                {
                    $update_config = array(
                        "fields" => $fields,
                        "types" => array(),
                        "values" => array(),
                    );
                    foreach($setTo as $index => $pair)
                    {
                        foreach($pair as $key => $value)
                        {
                            $update_config["types"][] = $value;
                            $update_config["values"][] = $key;
                        }
                    }
                    $where_fields = array();
                    $where_values = array();
                    $where_types = array();
                    $where_match = array();
                    foreach($wherefields as $data => $set)
                    {
                        foreach($set as $field => $match)
                        {
                            $where_fields[] = $field;
                            $where_match[] = $match;
                        }
                    }
                    foreach($wherevalues as $data => $set)
                    {
                        foreach($set as $value => $vtype)
                        {
                            $where_types[] = $vtype;
                            if($value === null) $value = "NULL";
                            $where_values[] = $value;
                        }
                    }
                    $where_config = array("join_with"=>$whereJoin,"fields"=>$where_fields,"values"=>$where_values,"types"=>$where_types,"matches"=>$where_match);
                    return $this->updateV2($table,$update_config,$where_config,$expected_changes);
                }
                else return $this->failure("Require at least one field you idiot.");
            }
            else return $this->failure("Please select a table first");
        }
        else return $this->failure("Unable to start sql. (Update)");
    }
}
?>
