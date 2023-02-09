<?php

namespace App\Switchboard;

use App\Config;
use YAPF\Bootstrap\Switchboard\Switchboard;

abstract class ConfigEnabled extends Switchboard
{
    protected Config $siteConfig;
    public function __construct()
    {
        global $system;
        $this->siteConfig = $system;
        parent::__construct();
    }
    protected function findMasterClass(): ?string
    {
        $this->loadingModule = ucfirst(strtolower($this->loadingModule));
        $this->loadingArea = ucfirst(strtolower($this->loadingArea));
        return parent::findMasterClass();
    }
}
