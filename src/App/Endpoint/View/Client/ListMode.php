<?php

namespace App\Endpoint\View\Client;

use App\Models\Sets\AvatarSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;

class ListMode extends RenderList
{
    public function process(): void
    {
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadAll("id", "DESC");
        $this->avatarSet = new AvatarSet();
        $this->avatarSet->loadByValues($this->rentalSet->getAllByField("avatarLink"));
        $this->streamSet = new StreamSet();
        $this->streamSet->loadByValues($this->rentalSet->getAllByField("streamLink"));

        $this->output->addSwapTagString("page_title", " [All]");
        parent::process();
    }
}
