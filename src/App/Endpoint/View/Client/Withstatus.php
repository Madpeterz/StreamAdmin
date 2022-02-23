<?php

namespace App\Endpoint\View\Client;

use App\Models\Sets\AvatarSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;

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
