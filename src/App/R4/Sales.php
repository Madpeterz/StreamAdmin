<?php

namespace App\R4;

use YAPF\DbObjects\GenClass\GenClass as GenClass;

// Do not edit this file, rerun gen.php to update!
class Sales extends genClass
{
    protected $use_table = "sales";
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "funds_new" => ["type" => "float", "value" => null],
        "funds_renew" => ["type" => "float", "value" => null],
        "tracking_new" => ["type" => "int", "value" => null],
        "tracking_renew" => ["type" => "int", "value" => null],
    ];
    public function getFunds_new(): ?float
    {
        return $this->getField("funds_new");
    }
    public function getFunds_renew(): ?float
    {
        return $this->getField("funds_renew");
    }
    public function getTracking_new(): ?int
    {
        return $this->getField("tracking_new");
    }
    public function getTracking_renew(): ?int
    {
        return $this->getField("tracking_renew");
    }
    /**
    * setFunds_new
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setFunds_new(?float $newvalue): array
    {
        return $this->updateField("funds_new", $newvalue);
    }
    /**
    * setFunds_renew
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setFunds_renew(?float $newvalue): array
    {
        return $this->updateField("funds_renew", $newvalue);
    }
    /**
    * setTracking_new
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTracking_new(?int $newvalue): array
    {
        return $this->updateField("tracking_new", $newvalue);
    }
    /**
    * setTracking_renew
    * @return mixed[] [status =>  bool, message =>  string]
    */
    public function setTracking_renew(?int $newvalue): array
    {
        return $this->updateField("tracking_renew", $newvalue);
    }
}
// please do not edit this file