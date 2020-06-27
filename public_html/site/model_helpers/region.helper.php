<?php
class region_helper
{
    protected $region = null;
    function get_region() : region
    {
        return $this->region;
    }
    function load_or_create(string $regionname) : bool
    {
        $this->region = new region();
        if(strlen($regionname) > 3)
        {
            if($this->region->load_by_field("name",$regionname) == true)
            {
                return true;
            }
            else
            {
                $this->region = new region();
                $this->region->set_field("name",$regionname);
                $save_status = $this->region->create_entry();
                return $save_status["status"];
            }
        }
        return false;
    }
}
?>
