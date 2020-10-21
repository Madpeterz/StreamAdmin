<?php
abstract class genClass_load extends genClass_setvalue
{
    protected function process_load(array $load_data) : bool
    {
        if($load_data["status"] == true)
        {
            if(count($load_data["dataSet"]) == 1)
            {
                $id_check_passed = true;
                $this->setup($load_data["dataSet"][0]);
                if(require_id_on_load == true)
                {
                    if($this->get_id() <= 0) $id_check_passed = false;
                }
                return $id_check_passed;
            }
            else if(count($load_data["dataSet"]) > 1)
            {
                $this->addError(__FILE__, __FUNCTION__, "Attempt to load multiple entrys into solo storage, please use the set collector X_set found ".count($load_data["dataSet"])." matches only allowed 1");
            }
        }
        return false;
    }
    public function load_targeted(array $wherefields=array(),array $wherevalues=array(),string $joinword="AND") :bool
    {
        $whereconfig = array(
            "join_with" => $joinword,
             "fields"=>array_keys($wherefields),
             "matches"=>array_values($wherefields),
             "values"=>array_keys($wherevalues),
             "type"=>array_values($wherevalues)
         );
        return $this->load_data($whereconfig);
    }
    protected function load_data(array $whereconfig=array()) :bool
    {
        if($this->disabled == false)
        {
            $options_config = array(
                "page_number" => 0,
                "max_entrys" => 1
            );
            $load_data = $this->sql->selectV2(array("table"=>$this->get_table()),null,$whereconfig,$options_config);
            return $this->process_load($load_data);
        }
        else $this->addError(__FILE__, __FUNCTION__, " this class is disabled");
        return false;
    }
    public function load_on_field(string $field_name,string $field_value) :bool
    {
        return $this->load_by_field($field_name,$field_value);
    }
	public function load_by_field(string $field_name,string $field_value) :bool
	{
		$field_type = $this->get_field_type($field_name,true);
		if($field_type !== false)
		{
            $whereconfig = array(
                 "fields"=>array($field_name),
                 "matches"=>array("="),
                 "values"=>array($field_value),
                 "types"=>array($field_type)
             );
			return $this->load_data($whereconfig);
		}
		else
		{
            $this->addError("Attempted to get field type: ".$field_name." but its not supported!");
			return false;
		}
	}
    public function load_with_config(array $where_config=array()) :bool
    {
        return $this->load_data($where_config);
    }
    public function load($id=0)
    {
        $whereconfig = array(
             "fields"=>array("id"),
             "matches"=>array("="),
             "values"=>array($id),
             "types"=>array("i")
         );
        return $this->load_data($whereconfig);
    }
}
?>
