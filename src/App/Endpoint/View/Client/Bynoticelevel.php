<?php

namespace App\Endpoint\View\Client;

use App\Models\Notice;

class Bynoticelevel extends RenderList
{
    public function process(): void
    {
        $notice = new Notice();
        $notice->loadID($this->siteConfig->getPage());
        $this->rentalSet = $notice->relatedRental();
        $this->avatarSet = $this->rentalSet->relatedAvatar();
        $this->streamSet = $this->rentalSet->relatedStream();
        $this->output->addSwapTagString("page_title", " By notice level: " . $notice->getName());
        parent::process();
    }
}
