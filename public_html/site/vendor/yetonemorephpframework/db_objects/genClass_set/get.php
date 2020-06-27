<?php
abstract class genClass_collection_get extends genClass_collectionSet
{
    public function get_count() : int
    {
        return count($this->collected);
    }
    public function get_collection() : array
    {
        // please use get_all_ids and then get_object_id to avoid unneeded memory usage.
        // this function should only be used on shit tables that dont have a unique index.
        return array_values($this->collected);
    }
    public function get_linked_array($left_side_field="",$right_side_field="") : array
    {
        $return_array = array();
        $left_side_field = "get_".$left_side_field."";
        $right_side_field = "get_".$right_side_field."";
        $worker = new $this->worker_class();
        if(method_exists($worker,$left_side_field))
        {
            if(method_exists($worker,$right_side_field))
            {
                foreach($this->collected as $object)
                {
                    $return_array[$object->$left_side_field()] = $object->$right_side_field();
                }
            }
            else
            {
                $this->addError(__FILE__,__FUNCTION__,"right side: ".$right_side_field." Not a function");
            }
        }
        else
        {
            $this->addError(__FILE__,__FUNCTION__,"left side: ".$left_side_field." Not a function");
        }
        return $return_array;
    }
    public function get_unique_array($field_name="") : array
    {
        $found_values = array();
        $function = "get_".$field_name."";
        foreach($this->collected as $key => $object)
        {
            $value = $object->$function();
            if(in_array($value,$found_values) == false)
            {
                $found_values[] = $value;
            }
        }
        return $found_values;
    }
    public function get_table() : ?string
	{
		if($this->worker_class != null)
		{
			$worker = new $this->worker_class();
			return $worker->get_table();
		}
		else
		{
			$this->addError(__FILE__,__FUNCTION__,"!!Critical error!!: attempting to call get_table from collection_set without a worker attached!");
			return null;
		}
	}
    public function get_first() : ?object
    {
        $return_obj = null;
        foreach($this->collected as $key => $value)
        {
            $return_obj = $value;
            break;
        }
        return $return_obj;
    }

    protected $fast_get_object_array_indexs = array();
    protected $fast_get_object_array_dataset = array();

    public function build_object_get_index(string $fieldname="",bool $force_rebuild=false)
    {
        if($this->worker == null) $this->worker = new $this->worker_class();
        if((in_array($fieldname,$this->fast_get_object_array_indexs ) == false) || ($force_rebuild == true))
        {
            $loadstring = "get_".$fieldname."";
            if(method_exists($this->worker,$loadstring))
            {
                $this->fast_get_object_array_indexs[] = $fieldname;
                $index = array();
                foreach($this->collected as $key => $object)
                {
                    $index[$object->$loadstring()] = $object->get_id();
                }
                $this->fast_get_object_array_dataset[$fieldname] = $index;
            }
        }
    }
    protected $shit_index = "";
    protected $shit_index_dataset = array();
    protected function build_shit_index(string $fieldname,$value)
    {
        if($this->shit_index != $fieldname)
        {
            if($this->worker == null) $this->worker = new $this->worker_class();
            $this->shit_index_dataset = array();
            $this->shit_index = $fieldname;
            $loadstring = "get_".$fieldname."";
            if(method_exists($this->worker,$loadstring))
            {
                foreach($this->collected as $index => $obj)
                {
                    $this->shit_index_dataset[$obj->$loadstring()] = $obj;
                }
            }
        }
    }
    protected function shit_index_search($value) : ?object
    {
        if(array_key_exists($value,$this->shit_index_dataset) == true) return $this->shit_index_dataset[$value];
        else return null;
    }
    protected function find_object_by_field(string $fieldname,$value) : ?object
    {
        if($this->worker == null) $this->worker = new $this->worker_class();
        $use_field = "id";
        if($this->worker->bad_id == true) $use_field = $this->worker->use_id_field;
        if($use_field == "id")
        {
            $this->build_object_get_index($fieldname);
            if(in_array($fieldname,$this->fast_get_object_array_indexs) == true)
            {
                if(array_key_exists($value,$this->fast_get_object_array_dataset[$fieldname]) == true)
                {
                    return $this->get_object_by_id($this->fast_get_object_array_dataset[$fieldname][$value]);
                }
            }
            return null;
        }
        else
        {
            $this->build_shit_index($fieldname,$value);
            return $this->shit_index_search($value);
        }
    }

    public function get_object_by_field(string $fieldname,$value) : ?object
    {
        return $this->find_object_by_field($fieldname,$value);
    }
    public function get_object_by_id($id=null) : ?object
    {
        if($this->worker == null) $this->worker = new $this->worker_class();
        $use_field = "id";
        if($this->worker->bad_id == true) $use_field = $this->worker->use_id_field;
        if($use_field == "id")
        {
            if(array_key_exists($id,$this->collected) == true) return $this->collected[$id];
            else return null;
        }
        else return $this->find_object_by_field($use_field,$id);
	}
    public function get_all_by_field($fieldname="") : array
    {
        return $this->get_unique_array($fieldname);
    }
    public function get_all_ids() : array
    {
        if($this->worker == null) $this->worker = new $this->worker_class();
        $use_field = "id";
        if($this->worker->bad_id == true) $use_field = $this->worker->use_id_field;
        return $this->get_all_by_field($use_field);
    }

}
?>
