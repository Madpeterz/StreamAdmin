<?php

namespace App\Endpoint\Hudapi\Config;

use App\Template\SecondlifeHudAjax;

class Hudconfig extends SecondlifeHudAjax
{
    public function process(): void
    {
        $this->ok("Get config [ok]");
        $this->setSwapTag("AllowDiscord", $this->siteConfig->getSlConfig()->_HudAllowDiscord);
        $this->setSwapTag("AllowGroup", $this->siteConfig->getSlConfig()->_HudAllowGroup);
        $this->setSwapTag("AllowDetails", $this->siteConfig->getSlConfig()->_HudAllowDetails);
        $this->setSwapTag("AllowRenewal", $this->siteConfig->getSlConfig()->_HudAllowRenewal);
        if ($this->siteConfig->getSlConfig()->_HudAllowDetails == false) {
            $this->setSwapTag("AllowRenewal", false);
        }
        if ($this->siteConfig->getSlConfig()->_LimitTime == true) {
            $this->setSwapTag("AllowRenewal", false);
        }
        $this->setSwapTag("GroupLink", $this->siteConfig->getSlConfig()->_HudGroupLink);
        $this->setSwapTag("DiscordLink", $this->siteConfig->getSlConfig()->_HudDiscordLink);
    }
}
