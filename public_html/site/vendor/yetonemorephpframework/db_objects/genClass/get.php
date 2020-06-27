<?php
abstract class genClass_get extends genClass_core
{
    public function create_uid(string $onfield,int $length,int $maxattempts) : array
    {
        return $this->overload_create_uid($onfield,$length,$maxattempts,0,"");
    }
    protected function overload_create_uid(string $onfield,int $length,int $max_attempts,int $attempts,string $last_uid) : array
    {
        $testuid = substr(md5("".time()."".microtime()."".rand(200,300)."".$last_uid.""),0,$length);
        $field = array("id");
        $wherefields = array(array($onfield => "="));
        $wherevalues = array(array($testuid => "s"));
        $count_check = $this->sql->basic_count($this->get_table(),$wherefields,$wherevalues,"AND");
        if($count_check["status"] == true)
        {
            if($count_check["count"] == 0)
            {
                return array("status"=>true,"uid"=>$testuid);
            }
            else
            {
                if($attempts < $max_attempts)
                {
                    $attempts++;
                    return $this->overload_create_uid($onfield,$attempts,$max_attempts,$testuid);
                }
                else
                {
                    return array("status"=>false,"message"=>"Attempt to create a uid timed out or failed");
                }
            }
        }
        else
        {
            return array("status"=>false,"message"=>"Attempt to create a uid timed out or failed");
        }
    }

    public function get_hash(array $exclude_fields=array("id")) : string
    {
        // if you flag use_dataset as false then the data will pull from the unsaved save_dataset not the matching
        // entry in the DB!
        $bits = array();
        foreach($this->get_fields() as $fieldname)
        {
            if(in_array($fieldname,$exclude_fields) == false)
            {
                $bits[] = $this->get_field($fieldname);
            }
        }
        return hash("sha256",implode("||",$bits));
    }
    public function object_to_mapped_array() : array
    {
        $reply = array();
        foreach(array_keys($this->dataset) as $fieldname)
        {
            $reply[$fieldname] = $this->get_field($fieldname);
        }
        return $reply;
    }
    public function object_to_value_array() : array
    {
        return array_values($this->object_to_mapped_array());
    }
    public function has_field(string $fieldname) : bool
    {
        return array_key_exists($fieldname,$this->dataset);
    }
    public function get_field_type(string $fieldname,bool $as_mysqli_code=false) : ?string
	{
		if(array_key_exists($fieldname,$this->dataset))
		{
			if($as_mysqli_code == false)
			{
				return $this->dataset[$fieldname]["type"];
			}
			else
			{
				 if($this->dataset[$fieldname]["type"] == "str") return "s";
                 else if($this->dataset[$fieldname]["type"] == "float") return "d";
				 else return "i";
			}

		}
		else
		{
			$this->addError(__FILE__, __FUNCTION__, "".get_class($this)." Attempting to read a fieldtype [".$fieldname."] that does not exist");
        }
		return null;
	}
    public function get_id()
    {
        if($this->bad_id == false) return $this->get_field("id");
        else return $this->get_field($this->use_id_field);
    }
    public function get_fields() :array
    {
        return array_keys($this->dataset);
    }
    public function is_loaded() : bool
    {
        if(array_key_exists("id",$this->dataset) == true)
        {
            if($this->get_field("id") > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    public function get_field(string $fieldname)
    {
        if($this->allow_set_field)
        {
            if(array_key_exists($fieldname,$this->dataset))
            {
                // correct the return to match the expected type (int,bool)
                $value = $this->dataset[$fieldname]["value"];
                if($value === null) return null;
                else
                {
                    if($this->dataset[$fieldname]["type"] == "int") return intval($value);
                    else if($this->dataset[$fieldname]["type"] == "bool")
                    {
                        if(($value === 1) || ($value === "1") || ($value === "true") || ($value === true) || ($value === "yes")) return true;
                        else return false;
                    }
                    else if($this->dataset[$fieldname]["type"] == "float") return floatval($value);
                    else return $value;
                }
            }
            else
            {
                $this->addError(__FILE__, __FUNCTION__, "".get_class($this)." Attempting to read a field [".$fieldname."] from a unloaded object, please check the code");
            }
        }
        else
        {
            $this->addError(__FILE__, __FUNCTION__, "Sorry this collection does not allow you to use the get_field function please call the direct object only!");
        }
        return null;
    }
    public function get_table() :string
    {
        return $this->use_table;
    }
}
?>
