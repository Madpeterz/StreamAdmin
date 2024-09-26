<?php

/*
    App/Config.php
    flags and settings for the website
*/

namespace App;

use App\Framework\SessionControl;
use App\Models\Slconfig;
use App\Models\Timezones;
use YAPF\Bootstrap\ConfigBox\BootstrapConfigBox;

class Config extends BootstrapConfigBox
{
    protected function loadURL(string $process = null): void
    {
        parent::loadURL($process);
        $this->setFlag("SITE_NAME", "Streamadmin");
        $this->setFlag("SITE_URL", "http://localhost/");
    }
    public function run(): void
    {
        $timeszone = new Timezones();
        $timeszone->loadID($this->getSlConfig()->getDisplayTimezoneLink());
        date_default_timezone_set($timeszone->getCode());
        $this->getSession()?->loadFromSession();
    }

    protected ?Slconfig $slConfig = null;

    public function &getSlConfig(): Slconfig
    {
        if ($this->slConfig == null) {
            $this->slConfig = new Slconfig();
            $this->slConfig->loadID(1);
        }
        if ($this->slConfig->isLoaded() == false) {
            die("SL Config not loaded - Please contact support load error: "
                . $this->slConfig->getLastErrorBasic());
        }
        return $this->slConfig;
    }

    public function forceProcessURI(string $uri): void
    {
        $this->loadURL($uri);
    }

    public function setPage(string $page): void
    {
        $this->page = $page;
    }

    public function setOption(string $option): void
    {
        $this->option = $option;
    }

    public function setArea(string $area): void
    {
        $this->area = $area;
    }

    public function shutdown(): void
    {
        parent::shutdown();
        $this->session = null;
    }

    protected ?SessionControl $session = null;

    public function &getSession(): ?SessionControl
    {
        if (($this->session == null) && ($this->enableRestart == true)) {
            $this->addError("No SessionControl - creating new");
            $this->session = new SessionControl(); // session super object
        }
        return $this->session;
    }

    public function unixtimeMin(): int
    {
        return 60;
    }

    public function unixtimeHour(): int
    {
        return $this->unixtimeMin() * 60;
    }

    public function unixtimeDay(): int
    {
        return $this->unixtimeHour() * 24;
    }

    public function unixtimeWeek(): int
    {
        return $this->unixtimeDay() * 7;
    }

    public function unixtimeYearAndHalf(): int
    {
        return (($this->unixtimeDay() * 31) * 18);
    }
}
