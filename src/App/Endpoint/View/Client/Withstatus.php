<?php

namespace App\Endpoint\View\Client;

use App\R7\Set\AvatarSet;
use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;

abstract class Withstatus extends RenderList
{
    protected array $whereconfig = [];
    public function process(): void
    {
        if (count($this->whereconfig) > 0) {
            $this->rentalSet = new RentalSet();
            $this->rentalSet->loadWithConfig($this->whereconfig);
            $this->avatarSet = new AvatarSet();
            $this->avatarSet->loadByValues($this->rentalSet->getAllByField("avatarLink"));
            $this->streamSet = new StreamSet();
            $this->streamSet->loadByValues($this->rentalSet->getAllByField("streamLink"));
        }
        parent::process();
    }
}
