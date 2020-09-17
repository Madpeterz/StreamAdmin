<?php
class object_helper
{
    protected $object = null;
    function get_object() : objects
    {
        return $this->object;
    }
    function load_or_create(int $avatar_id,int $region_id,string $objectuuid,string $objectname,string $objectmode,string $pos,bool $show_errors=false) : bool
    {
        $this->object = new objects();
        if(strlen($objectuuid) == 36)
        {
            if($this->object->load_by_field("objectuuid",$objectuuid) == true)
            {
                $this->object->set_lastseen(time());
                if($this->object->get_regionlink() != $region_id)
                {
                    $this->object->set_regionlink($region_id);
                }
                $save_status = $this->object->save_changes();
                if($save_status["status"] == false)
                {
                    if($show_errors == true) print "[Objects helper] - ".$save_status["message"];
                }
                return $save_status["status"];
            }
            else
            {
                $this->object = new objects();
                $this->object->set_avatarlink($avatar_id);
                $this->object->set_regionlink($region_id);
                $this->object->set_objectuuid($objectuuid);
                $this->object->set_objectname($objectname);
                $this->object->set_objectmode($objectmode);
                $this->object->set_objectxyz($pos);
                $this->object->set_lastseen(time());
                $save_status = $this->object->create_entry();
                if($save_status["status"] == false)
                {
                    if($show_errors == true) print "[Objects helper] - ".$save_status["message"];
                }
                return $save_status["status"];
            }
        }
        else
        {
            if($show_errors == true) print "[Objects helper] - Object UUID length must be 36";
        }
        return false;
    }
}
?>
