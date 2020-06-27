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
                $this->object->set_field("lastseen",time());
                if($this->object->get_regionlink() != $region_id)
                {
                    $this->object->set_field("regionlink",$region_id);
                }
                $save_status = $this->object->save_changes();
                if($save_status["status"] == false)
                {
                    if($show_errors == true) echo "[Objects helper] - ".$save_status["message"];
                }
                return $save_status["status"];
            }
            else
            {
                $this->object = new objects();
                $this->object->set_field("avatarlink",$avatar_id);
                $this->object->set_field("regionlink",$region_id);
                $this->object->set_field("objectuuid",$objectuuid);
                $this->object->set_field("objectname",$objectname);
                $this->object->set_field("objectmode",$objectmode);
                $this->object->set_field("objectxyz",$pos);
                $this->object->set_field("lastseen",time());
                $save_status = $this->object->create_entry();
                if($save_status["status"] == false)
                {
                    if($show_errors == true) echo "[Objects helper] - ".$save_status["message"];
                }
                return $save_status["status"];
            }
        }
        else
        {
            if($show_errors == true) echo "[Objects helper] - Object UUID length must be 36";
        }
        return false;
    }
}
?>
