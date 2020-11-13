<?php

namespace App\View\Avatar;

use App\View\Client\RenderList;
use App\AvatarSet;
use App\RentalSet;
use App\StreamSet;

class ListMode extends RenderList
{
    public function process()
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
