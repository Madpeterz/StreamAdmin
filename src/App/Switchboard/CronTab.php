<?php

namespace App\Switchboard;

class CronTab extends ConfigEnabled
{
    protected string $targetEndpoint = "CronJob";
    protected function accessChecks(): bool
    {
        $options = $this->getOpts();
        if (array_key_exists("t", $options) == false) {
            return false;
        }
        $this->loadingModule = "Tasks";
        $this->loadingArea = $options["t"];
        return true;
    }
}
