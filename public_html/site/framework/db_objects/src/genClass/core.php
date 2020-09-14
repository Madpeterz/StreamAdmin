<?php
abstract class sql_connected_class extends error_logging
{
    protected $sql = null;
    protected $disabled = false;
    function __construct()
    {
        global $sql;
        if($this->disabled == false)
        {
            $this->sql = $sql;
        }
    }

}
abstract class genClass_core extends sql_connected_class
{
    protected $use_table = "";
    protected $save_dataset = array();
    protected $dataset = array();
    protected $allow_set_field = true;
    public $bad_id = false;
    public $use_id_field = "";
    function __construct(array $defaults=array())
    {
        if(count($defaults) > 0)
        {
            $this->setup($defaults);
        }
        parent::__construct();
    }
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
    function has_field(string $field_name) : bool
    {
        return array_key_exists($field_name,$this->dataset);
    }
}
?>
