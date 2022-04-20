<?php

namespace App\Models;

use YAPF\Framework\DbObjects\GenClass\GenClass as GenClass;
use YAPF\Framework\Responses\DbObjects\UpdateReply as UpdateReply;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply as SingleLoadReply;
use App\Models\Sets\RentalSet as RentalSet;

// Do not edit this file, rerun gen.php to update!
class Detail extends genClass
{
    protected $use_table = "detail";
    // Data Design
    protected $fields = [
        "id",
        "rentalLink",
    ];
    protected $dataset = [
        "id" => ["type" => "int", "value" => null],
        "rentalLink" => ["type" => "int", "value" => null],
    ];
    // Setters
    /**
    * setRentalLink
    */
    public function setRentalLink(?int $newValue): UpdateReply
    {
        return $this->updateField("rentalLink", $newValue);
    }
    // Getters
    public function getRentalLink(): ?int
    {
        return $this->getField("rentalLink");
    }
    // Loaders
    public function loadByRentalLink(int $rentalLink): SingleLoadReply
    {
        return $this->loadByField(
            "rentalLink",
            $rentalLink
        );
    }
    public function relatedRental(): RentalSet
    {
        $ids = [$this->getRentalLink()];
        $collection = new RentalSet();
        $collection->loadFromIds($ids);
        return $collection;
    }
}
// please do not edit this file
