<?php

namespace App\Endpoint\View\Client;

use App\R7\Set\AvatarSet;
use App\R7\Model\Notice;
use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;

class Bynoticelevel extends RenderList
{
    public function process(): void
    {
        $notice = new Notice();
        $notice->loadID($this->page);
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadByField("noticeLink", $this->page);
        $this->avatarSet = new AvatarSet();
        $this->avatarSet->loadIds($this->rentalSet->getAllByField("avatarLink"));
        $this->streamSet = new StreamSet();
        $this->streamSet->loadIds($this->rentalSet->getAllByField("streamLink"));
        $this->output->addSwapTagString("page_title", " By notice level: " . $notice->getName());
        parent::process();
    }
}
