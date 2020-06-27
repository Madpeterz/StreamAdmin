<?php
abstract class genClass_load extends genClass_set
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
    public function load_targeted(array $wherefields=array(),array $wherevalues=array(),string $joinword="AND",string $orderBy = "",string $orderDir = "DESC") :bool
    {
        return $this->load_data($wherefields,$wherevalues,$joinword,$orderBy,$orderDir,1);
    }
    protected function load_data(array $wherefields=array(),array $wherevalues=array(),string $joinword="AND",string $orderBy = "",string $orderDir = "DESC",int $limit=12) :bool
    {
        if($this->disabled == false)
        {
            $fields = array_keys($this->dataset);
            $load_data = $this->sql->select($this->get_table(),$fields,$wherefields,$wherevalues,$joinword,$orderBy,$orderDir,$limit);
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
			return $this->load_data(array(array($field_name => "=")),array(array($field_value => $field_type)));
		}
		else
		{
            $this->addError("Attempted to get field type: ".$field_name." but its not supported!");
			return false;
		}
	}
    public function load_with_config(array $where_config=array()) :bool
    {
        $load_data = $this->sql->selectV2(
            array("table"=>$this->get_table()),
            null,
            $where_config
        );
        return $this->process_load($load_data);
    }
    public function load($id=0)
    {
        return $this->load_data(array(array("id" => "=")),array(array($id => "i")));
    }
}
?>
