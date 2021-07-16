<?php

namespace App\Endpoint\View\Client;

use App\R7\Set\AvatarSet;
use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;

class ListMode extends RenderList
{
    public function process(): void
    {
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadAll("id", "DESC");
        $this->avatarSet = new AvatarSet();
        $this->avatarSet->loadIds($this->rentalSet->getAllByField("avatarLink"));
        $this->streamSet = new StreamSet();
        $this->streamSet->loadIds($this->rentalSet->getAllByField("streamLink"));

        $this->output->addSwapTagString("page_title", " [All]");
        parent::process();
    }
}
