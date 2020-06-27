<?php
abstract class genClass_collection_updatebulk extends genClass_collection_get
{
    public function update_single_field_for_collection($update_field="",$new_value="")
    {
        return $this->update_multiple_fields_for_collection(array($update_field),array($new_value));
    }
    public function update_multiple_fields_for_collection($update_fields=array(),$new_values=array())
    {
        if($this->get_count() > 0)
        {
            if($this->worker_class != null)
            {
                $worker = new $this->worker_class();

                $wherefields = array();
                $wherevalues = array();
                $setfields = array();
                $setvalues = array();
                $loop = 0;
                $all_ok = true;
                $lookup_failed_on = "";
                while($loop < count($update_fields))
                {
                    $lookup = "get_".$update_fields[$loop]."";
                    if(method_exists($worker,$lookup))
                    {
                        $field_type = $worker->get_field_type($update_fields[$loop],true);
                        if($field_type != null)
                        {
                            $setfields[] = $update_fields[$loop];
                            $setvalues[] = array($new_values[$loop] => $field_type);
                        }
                        else
                        {
                            $lookup_failed_on = "Unable to find fieldtype for: ".$update_fields[$loop]."";
                            $all_ok = false;
                            break;
                        }
                    }
                    else
                    {
                        $lookup_failed_on = "Unable to find method: ".$lookup."";
                        $all_ok = false;
                        break;
                    }
                    $loop++;
                }
                if($all_ok == true)
                {
                    $expected_changes = 0;
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
                                $wherefields[] = array("id" => "=");
                                $wherevalues[] = array($entry_id => "i");
                                break;
                            }
                            $loop2++;
                        }
                    }
                    if($expected_changes > 0)
                    {
                        return $this->sql->update($worker->get_table(), $setfields, $setvalues, $wherefields, $wherevalues, "OR",$expected_changes);
                    }
                    else return array("status"=>true,"changes"=>0,"message"=>"no changes to be made");
                }
                else return array("status"=>false,"changes"=>0,"message"=>$lookup_failed_on);
            }
            else return array("status"=>false,"changes"=>0,"message"=>"worker not setup");
        }
        else return array("status"=>false,"changes"=>0,"message"=>"Nothing loaded in collection");
    }
}
?>
