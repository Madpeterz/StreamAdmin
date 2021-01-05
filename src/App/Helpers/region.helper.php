<?php

class region_helper
{
    protected $region = null;
    function get_region(): region
    {
        return $this->region;
    }
    function load_or_create(string $regionname): bool
    {
        $this->region = new region();
        if (strlen($regionname) > 3) {
            if ($this->region->loadByField("name", $regionname) == true) {
                return true;
            } else {
                $this->region = new region();
                $this->region->setName($regionname);
                $save_status = $this->region->createEntry();
                return $save_status["status"];
            }
        }
        return false;
    }
}