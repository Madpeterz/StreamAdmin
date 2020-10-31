<?php
abstract class genClass_collection_functions  extends genClass_collection_load
{
    protected $built_search_index_level_1 = "";
    protected $built_search_index = []; // objectid => value
    protected function indexed_search($target_value)
    {
        $matched_object = null;
        if(in_array($target_value,array_values($this->built_search_index)) == true)
        {
            foreach($this->built_search_index as $object_id => $value)
            {
                if($value == $target_value)
                {
                    $matched_object = $this->collected[$object_id];
                    break;
                }
            }
        }
        return $matched_object;
    }
    public function add_to_collected(genClass $object)
    {
        $this->collected[$object->get_id()] = $object;
    }
    protected function create_fast_index(string $target_field)
    {
        $this->built_search_index = []; // reset the index
        foreach($this->collected as $obj)
        {
            $function = "get_".$target_field."";
            $this->built_search_index[$obj->get_id()] = $obj->$function();
        }
        $this->built_search_index_level_1 = $target_field;
    }

    public function search($search_settings=[])
    {
        // array("wp_parent_id" => $wp_post->get_id(),...)
        $matched_object = null;
        if(count($search_settings) == 1)
        {
            $search_key = "";
            $search_value = "";
            foreach($search_settings as $search_field => $search_value_find)
            {
                $search_key = $search_field;
                $search_value = $search_value_find;
                break;
            }
            // searchs the collection for the first matching object based on the search settings
            if($this->built_search_index_level_1 == $search_key)
            {
                // use the fast index
                return $this->indexed_search($search_value);
            }
            else
            {
                // create and then use the fast index
                $this->create_fast_index($search_key);
                return $this->indexed_search($search_value);
            }
        }
        else
        {
            // slow deep search
            foreach($this->collected as $key => $object)
            {
                $all_ok = true;
                foreach($search_settings as $search_field => $search_value)
                {
                    $function = "get_".$search_field."";
                    if($object->$function() != $search_value)
                    {
                        $all_ok = false;
                        break;
                    }
                }
                if($all_ok == true)
                {
                    $matched_object = $object;
                    break;
                }
            }
        }
        return $matched_object;
    }
}
?>
