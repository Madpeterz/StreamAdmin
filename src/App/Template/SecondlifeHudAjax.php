<?php

namespace App\Template;

abstract class SecondlifeHudAjax extends SecondlifeAjax
{
    protected function hashCheck(): void
    {
        if ($this->load_ok == false) {
            return;
        }
        $raw = $this->unixtime . "" . $this->staticpart . "" . $this->slconfig->getHudLinkCode();
        $hashcheck = sha1($raw);
        if ($hashcheck != $this->hash) {
            $this->load_ok = false;
            $this->setSwapTag("message", "Unable to vaildate request to API endpoint: ");
            return;
        }
        $this->continueHashChecks(true);
    }
}
