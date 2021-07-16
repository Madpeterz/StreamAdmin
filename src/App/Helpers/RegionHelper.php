<?php

namespace App\Helpers;

use App\R7\Model\Region;

class RegionHelper
{
    protected $region = null;
    public function getRegion(): region
    {
        return $this->region;
    }
    public function loadOrCreate(string $regionname): bool
    {
        $this->region = new Region();
        if (strlen($regionname) > 3) {
            if ($this->region->loadByField("name", $regionname) == true) {
                return true;
            } else {
                $this->region = new Region();
                $this->region->setName($regionname);
                $save_status = $this->region->createEntry();
                return $save_status["status"];
            }
        }
        return false;
    }
}
