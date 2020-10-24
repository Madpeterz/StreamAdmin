<?php
abstract class genClass_setvalue extends genClass_getvalue
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
    public function set_id(?int $newvalue) : array
    {
        if($this->bad_id == false)
        {
            return $this->update_field("id",$newvalue,true);
        }
        else
        {
            return array("status"=>false,"message"=>"Support for bad ids no just no");
        }
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
            $reply = $this->update_field($fieldname,$value,true);
            if($reply["status"] == false)
            {
                $status = false;
                $message = $reply["message"];
                break;
            }
        }
        return array("status"=>$status,"message"=>$message);
    }
    /*
    find: (set_field\(\")(.*)(\",)
    replace: set_$2(
    dirs: !public_html\site\model, !public_html\site\vendor
    */
    public function set_field(string $fieldname="",$value=null,bool $ignore_set_id_warning=false) :array
    {
        trigger_error("set_field is being phased out please use set_[fieldname]",E_USER_DEPRECATED);
        return $this->update_field($fieldname,$value,$ignore_set_id_warning);
    }
    protected function update_field(string $fieldname="",$value=null,bool $ignore_set_id_warning=false) :array
    {
        $errored_on = "";
        if(count($this->dataset) != count($this->save_dataset))
        {
            //$this->addError(__FILE__, __FUNCTION__, "save_dataset is out of sync this should never happen!");
            $this->save_dataset = $this->dataset;
        }
        if(is_object($value) == true)
        {
            $errored_on = "System error: Attempt to put a object onto field: ".$fieldname;
        }
        else if(is_array($value) == true)
        {
            $errored_on = "System error: Attempt to put a array onto field: ".$fieldname;
        }
        else if($this->disabled == true)
        {
            $errored_on = "This class is disabled";
        }
        else if($this->allow_set_field == false)
        {
            $errored_on = "update_field is not allowed for this object";
        }
        else if(array_key_exists($fieldname,$this->dataset) == false)
        {
            $errored_on = "Sorry this object does not have the field: ".$fieldname;
        }
        else if(($fieldname == "id") && ($ignore_set_id_warning == false))
        {
            $errored_on = "Sorry this object does not allow you to set the id field!";
        }
        if($errored_on == "")
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
            $this->addError(__FILE__, __FUNCTION__, $errored_on);
            return array("status"=>false,"message"=>$this->myLastError);
        }
    }
}
?>
