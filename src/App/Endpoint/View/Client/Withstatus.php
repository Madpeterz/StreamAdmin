<?php

namespace App\Endpoint\View\Client;

use App\Models\Set\RentalSet;

abstract class Withstatus extends RenderList
{
    protected array $whereconfig = [];
    public function process(): void
    {
        if (count($this->whereconfig) > 0) {
            $this->rentalSet = new RentalSet();
            $this->rentalSet->loadWithConfig($this->whereconfig);
            $this->avatarSet = $this->rentalSet->relatedAvatar();
            $this->streamSet = $this->rentalSet->relatedStream();
        }
        parent::process();
    }
}
