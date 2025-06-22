<?php

namespace App\Endpoint\View\Client;

use App\Models\Set\RentalSet;

class ListMode extends RenderList
{
    public function process(): void
    {
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadAll("id", "DESC");
        $this->avatarSet = $this->rentalSet->relatedAvatar();
        $this->streamSet = $this->rentalSet->relatedStream();
        $this->output->addSwapTagString("page_title", " [All]");
        parent::process();
    }
}
