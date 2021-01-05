<?php

namespace App\Endpoints\View\Avatar;

use App\Endpoints\View\Client\RenderList;
use App\Models\AvatarSet;
use App\Models\RentalSet;
use App\Models\StreamSet;

class ListMode extends RenderList
{
    public function process(): void
    {
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadAll("id", "DESC");
        $this->avatarSet = new AvatarSet();
        $this->avatarSet->loadIds($this->rentalSet->getAllByField("avatarlink"));
        $this->streamSet = new StreamSet();
        $this->streamSet->loadIds($this->rentalSet->getAllByField("streamlink"));

        $this->output->addSwapTagString("page_title", " [All]");
        parent::process();
    }
}
