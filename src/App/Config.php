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
    public function __construct()
    {
        parent::__construct();
        // left ENV name, Right Dev default
        // if you are running this in classic mode [ie cpanel]
        // you will need to update the values on the right
        $this->setFlag("SITE_NAME", "Streamadmin");
        $this->setFlag("SITE_URL", "https://dev.blackatom.live/");
        $timeszone = new Timezones();
        $timeszone->loadID($this->getSlConfig()->getDisplayTimezoneLink());
        date_default_timezone_set($timeszone->getCode());
    }

    protected ?Slconfig $slConfig = null;

    public function & getSlConfig(): Slconfig
    {
        if ($this->slConfig == null) {
            $this->slConfig = new Slconfig();
            $this->slConfig->loadID(1);
        }
        if ($this->slconfig->isLoaded() == false) {
            die("SL Config not loaded - Please contact support");
        }
        return $this->slConfig;
    }


    public function setupCacheTables(): void
    {
        if ($this->getCacheDriver() == null) {
            return;
        }
        $this->getCacheDriver()->addTableToCache("banlist", 120, true);
        $this->getCacheDriver()->addTableToCache("botconfig", 120, true);
        $this->getCacheDriver()->addTableToCache("noticenotecard", 120, true);
        $this->getCacheDriver()->addTableToCache("notice", 120, true, true);
        $this->getCacheDriver()->addTableToCache("package", 120, true, true);
        $this->getCacheDriver()->addTableToCache("region", 120, true);
        $this->getCacheDriver()->addTableToCache("reseller", 120, true, true);
        $this->getCacheDriver()->addTableToCache("server", 120, true, true);
        $this->getCacheDriver()->addTableToCache("servertypes", 120, true);
        $this->getCacheDriver()->addTableToCache("slconfig", 120, true, true);
        $this->getCacheDriver()->addTableToCache("template", 120, true, true);
        $this->getCacheDriver()->addTableToCache("textureconfig", 120, true, true);
        $this->getCacheDriver()->addTableToCache("timezones", 120, true, true);
        $this->getCacheDriver()->addTableToCache("treevender", 120, true);
        $this->getCacheDriver()->addTableToCache("treevenderpackages", 120, true);
    }

    public function getRedisHost(): ?string
    {
        return $this->getFlag("REDIS_HOST");
    }

    public function getCacheEnabled(): bool
    {
        return boolval($this->getFlag("ENABLE_CACHE"));
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
        $this->area = ucfirst($area);
    }

    public function shutdown(): void
    {
        parent::shutdown();
        $this->session = null;
    }

    protected ?SessionControl $session = null;

    public function & getSession(): ?SessionControl
    {
        if (($this->session == null) && ($this->enableRestart == true)) {
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
