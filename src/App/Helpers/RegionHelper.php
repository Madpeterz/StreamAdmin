<?php

namespace App\Helpers;

use App\Models\Region;

class RegionHelper
{
    protected $region = null;
    protected $lastError = "";
    public function getRegion(): region
    {
        return $this->region;
    }
    public function getLastError(): string
    {
        return $this->lastError;
    }
    public function loadOrCreate(string $regionname): bool
    {
        $this->region = new Region();
        if (strlen($regionname) < 3) {
            $this->lastError = "Region name to short";
            return false;
        }
        if ($this->region->loadByField("name", $regionname) == true) {
            return true;
        }
        $this->region = new Region();
        $this->region->setName($regionname);
        $save_status = $this->region->createEntry();
        if ($save_status["status"] == false) {
            $this->lastError = $save_status["message"];
        }
        return $save_status["status"];
    }
}
