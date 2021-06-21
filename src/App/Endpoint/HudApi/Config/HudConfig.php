<?php

namespace App\Endpoint\HudApi\Config;

use App\Template\SecondlifeHudAjax;

class HudConfig extends SecondlifeHudAjax
{
    public function process(): void
    {
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Get config [ok]");
        $this->setSwapTag("AllowDiscord", $this->slconfig->getHudAllowDiscord());
        $this->setSwapTag("AllowGroup", $this->slconfig->getHudAllowGroup());
        $this->setSwapTag("AllowDetails", $this->slconfig->getHudAllowDetails());
        $this->setSwapTag("AllowRenewal", $this->slconfig->getHudAllowRenewal());
        if ($this->slconfig->getHudAllowDetails() == false) {
            $this->setSwapTag("AllowRenewal", false);
        }
        $this->setSwapTag("GroupLink", $this->slconfig->getHudGroupLink());
        $this->setSwapTag("DiscordLink", $this->slconfig->getHudDiscordLink());
    }
}
