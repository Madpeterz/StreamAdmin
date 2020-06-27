<?php
abstract class genClass_set extends genClass_get
{
    public function setup(array $keyvalues=array()) :bool
    {
        foreach($keyvalues as $key => $value)
        {
            if(array_key_exists($key,$this->dataset))
            {
                $this->dataset[$key]["value"] = $value;
            }
        }
        $this->save_dataset = $this->dataset;
        return true;
    }
    public function set_table(string $tablename="")
    {
        $this->use_table = $tablename;
    }
    public function set_fields(array $keyvaluepairs) : array
    {
        $status = true;
        $message = "ok";
        foreach($keyvaluepairs as $fieldname => $value)
        {
            $reply = $this->set_field($fieldname,$value);
            if($reply["status"] == false)
            {
                $status = false;
                $message = $reply["message"];
                break;
            }
        }
        return array("status"=>$status,"message"=>$message);
    }
    public function set_field(string $fieldname="",$value=null,bool $ignore_set_id_warning=false) :array
    {
        if(is_object($value) == false)
        {
            if(is_array($value) == false)
            {
                if($this->disabled == false)
                {
                    if($this->allow_set_field)
                    {
                        if(array_key_exists($fieldname,$this->dataset))
                        {
                            if(count($this->dataset) != count($this->save_dataset))
                            {
                                $this->save_dataset = $this->dataset;
                            }
                            if(($fieldname != "id") || ($ignore_set_id_warning == true))
                            {
            	                 $this->dataset[$fieldname]["value"] = $value;
                                 if(($fieldname == "id") && ($ignore_set_id_warning == true))
                                 {
                                     // you should rly not be doing this unless you understand what this is doing ^+^
                                     $this->save_dataset["id"]["value"] = $value;
                                 }
            	                 return array("status"=>true,"message"=>"value set");
                            }
                            else
                            {
                                $this->addError(__FILE__, __FUNCTION__, "Fieldname: ".$fieldname." - Sorry this object does not allow you to set the id field!");
                            }
                        }
                        else
                        {
                            $this->addError(__FILE__, __FUNCTION__, "Fieldname: ".$fieldname." - Sorry this object does not have the field!");
                        }
                    }
                    else
                    {
                        $this->addError(__FILE__, __FUNCTION__, "Fieldname: ".$fieldname." - Sorry this collection does not allow you to use the set_field function please call the direct object only!");
                    }
                }
                else
                {
                    $this->addError(__FILE__, __FUNCTION__, " this class is disabled");
                }
                return array("status"=>false,"message"=>$this->myLastError);
            }
            else die("System error: Attempt to put a array onto field: ".$fieldname);
        }
        else die("System error: Attempt to put a object onto field: ".$fieldname);
        return array("status"=>false,"message"=>"This should not run its after dies");
    }
}
?>
