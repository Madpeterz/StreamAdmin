<?php
abstract class genClass_collectionSet extends error_logging
{
    protected $collected = array();
    protected $sql = null;
    protected $worker_class = null;
    protected $worker = null;

    function __construct()
    {
        global $sql;
        $this->sql = $sql;
        $this->worker_class = substr_replace(get_class($this), "", -4);
    }
    public function get_worker_class()
    {
        return $this->worker_class;
    }
    public function collection_hash() : string
    {
        $hash_builder = "";
        foreach($this->collected as $entry)
        {
            $hash_builder .= $entry->get_hash();
        }
        return hash("sha256",$hash_builder);
    }
    public function add_to_collected(genClass $object)
    {
        $this->collected[$object->get_id()] = $object;
    }
    protected function get_by_field(string $fieldname="",$value) : object
    {
        $found_entry = null;
        $function = "get_".$fieldname."";
        if($this->worker == null) $this->worker = new $this->worker_class();
        if(method_exists($this->worker,$function) == true)
        {
            foreach($this->collected as $point)
            {
                if($point->$function() == $value)
                {
                    $found_entry = $point;
                    break;
                }
            }
        }
        return $found_entry;
    }
    protected function process_load($load_data = array())
    {
        if($this->worker == null) $this->worker = new $this->worker_class();
        $use_field = "id";
        if($this->worker->bad_id == true) $use_field = $this->worker->use_id_field;
        foreach($load_data["dataSet"] as $entry)
        {
            $new_object = new $this->worker_class();
            if($new_object->setup($entry))
            {
                $id_check_passed = true;
                if(require_id_on_load == true)
                {
                    if($new_object->get_id() <= 0) $id_check_passed = false;
                }
                if($id_check_passed == true)
                {
                    if($use_field != null) $this->collected[$entry[$use_field]] = $new_object;
                    else $this->collected[count($this->collected)] = $new_object;
                }
                else
                {
                    return array("status"=>false,"count"=>count($this->collected),"message"=>"ok");
                }
            }
        }
        if(require_id_on_load == true)
        {
            return array("status"=>true,"count"=>count($this->collected),"message"=>"ok");
        }
        else
        {
            return array("status"=>false,"count"=>count($this->collected),"message"=>"ok");
        }
    }
}
?>
