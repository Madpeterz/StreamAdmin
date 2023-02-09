<?php

namespace App\Endpoint\Hudapi\Config;

use App\Template\SecondlifeHudAjax;

class Hudconfig extends SecondlifeHudAjax
{
    public function process(): void
    {
        $this->ok("Get config [ok]");
        $this->setSwapTag("AllowDiscord", $this->siteConfig->getSlConfig()->getHudAllowDiscord());
        $this->setSwapTag("AllowGroup", $this->siteConfig->getSlConfig()->getHudAllowGroup());
        $this->setSwapTag("AllowDetails", $this->siteConfig->getSlConfig()->getHudAllowDetails());
        $this->setSwapTag("AllowRenewal", $this->siteConfig->getSlConfig()->getHudAllowRenewal());
        if ($this->siteConfig->getSlConfig()->getHudAllowDetails() == false) {
            $this->setSwapTag("AllowRenewal", false);
        }
        $this->setSwapTag("GroupLink", $this->siteConfig->getSlConfig()->getHudGroupLink());
        $this->setSwapTag("DiscordLink", $this->siteConfig->getSlConfig()->getHudDiscordLink());
    }
}
