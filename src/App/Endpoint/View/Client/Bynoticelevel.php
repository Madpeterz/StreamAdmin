<?php

namespace App\Endpoint\View\Client;

use App\Models\Sets\AvatarSet;
use App\Models\Notice;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;

class Bynoticelevel extends RenderList
{
    public function process(): void
    {
        $notice = new Notice();
        $notice->loadID($this->siteConfig->getPage());
        $this->rentalSet = new RentalSet();
        $this->rentalSet->loadByField("noticeLink", $this->siteConfig->getPage());
        $this->avatarSet = new AvatarSet();
        $this->avatarSet->loadByValues($this->rentalSet->getAllByField("avatarLink"));
        $this->streamSet = new StreamSet();
        $this->streamSet->loadByValues($this->rentalSet->getAllByField("streamLink"));
        $this->output->addSwapTagString("page_title", " By notice level: " . $notice->getName());
        parent::process();
    }
}
