<?php
abstract class genClass_collection_updatebulk extends genClass_collection_get
{
    public function update_single_field_for_collection(string $update_field,?string $new_value) : array
    {
        return $this->update_multiple_fields_for_collection(array($update_field),array($new_value));
    }
    protected function update_multiple_make_update_config(array $update_fields,array $new_values) : array
    {
        $worker = new $this->worker_class();
        $update_config = array(
            "fields" => [],
            "values" => [],
            "types" => [],
        );
        $all_ok = true;
        $loop = 0;
        while($loop < count($update_fields))
        {
            $lookup = "get_".$update_fields[$loop]."";
            if(method_exists($worker,$lookup))
            {
                $field_type = $worker->get_field_type($update_fields[$loop],true);
                if($field_type != null)
                {
                    $update_config["fields"][] = $update_fields[$loop];
                    $update_config["values"][] = $new_values[$loop];
                    $update_config["types"][] = $field_type;
                }
                else
                {
                    $all_ok = false;
                    break;
                }
            }
            else
            {
                $all_ok = false;
                break;
            }
            $loop++;
        }
        return array("status"=>$all_ok,"dataset"=>$update_config);
    }
    protected function update_multiple_get_updated_ids(array $update_fields,array $new_values) : array
    {
        $expected_changes = 0;
        $changed_ids = [];
        foreach($this->get_all_ids() as $entry_id)
        {
            $localworker = $this->collected[$entry_id];
            $loop2 = 0;
            while($loop2 < count($update_fields))
            {
                $lookup = "get_".$update_fields[$loop2];
                if($localworker->$lookup() != $new_values[$loop2])
                {
                    $expected_changes++;
                    $changed_ids[] = $entry_id;
                    break;
                }
                $loop2++;
            }
        }
        return array("changes"=>$expected_changes,"changed_ids"=>$changed_ids);
    }
    protected function update_multiple_apply_changes(array $update_fields,array $new_values)
    {
        foreach($this->get_all_ids() as $entry_id)
        {
            $localworker = $this->collected[$entry_id];
            $loop2 = 0;
            while($loop2 < count($update_fields))
            {
                $applyer = "set_".$update_fields[$loop2];
                $localworker->$applyer($new_values[$loop2]);
                $loop2++;
            }
        }
    }
    public function update_multiple_fields_for_collection(array $update_fields,array $new_values) : array
    {
        if($this->get_count() > 0)
        {
            if(count($update_fields) > 0)
            {
                if($this->worker_class != null)
                {
                    $worker = new $this->worker_class();

                    $where_config = array(
                        "fields"=>array("id"),
                        "matches"=>array("IN"),
                        "values"=>[],
                        "types"=>array("i"),
                    );
                    $ready_update_config = $this->update_multiple_make_update_config($update_fields,$new_values);
                    if($ready_update_config["status"] == true)
                    {
                        $update_config = $ready_update_config["dataset"];
                        unset($ready_update_config);
                        $change_config = $this->update_multiple_get_updated_ids($update_fields,$new_values);
                        if($change_config["changes"] > 0)
                        {
                            $where_config["values"][] = $change_config["changed_ids"];
                            $update_status = $this->sql->updateV2($worker->get_table(),$update_config,$where_config,$change_config["changes"]);
                            unset($change_config);
                            if($update_status["status"] == true)
                            {
                                $this->update_multiple_apply_changes($update_fields,$new_values);
                                return $update_status;
                            }
                            return array("status"=>false,"changes"=>0,"message"=>"Update failed because:".$update_status["message"]);
                        }
                        return array("status"=>true,"changes"=>0,"message"=>"No changes made to collection");
                    }
                    return array("status"=>false,"changes"=>0,"message"=>"Unable to find all field types needed for update!");
                }
                return array("status"=>false,"changes"=>0,"message"=>"worker not setup");
            }
            return array("status"=>false,"changes"=>0,"message"=>"No fields being updated!");
        }
        return array("status"=>false,"changes"=>0,"message"=>"Nothing loaded in collection");
    }
}
?>
