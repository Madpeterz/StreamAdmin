<?php

/*
    App/Config.php
    flags and settings for the website
*/
namespace App;

use App\Framework\SessionControl;
use App\Models\Slconfig;
use YAPF\Bootstrap\ConfigBox\BootstrapConfigBox;
use YAPF\Framework\Cache\Drivers\Redis;

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
        $timeszone->loadID($slconfig->getDisplayTimezoneLink());
        $timezone_name = $timeszone->getName();
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
        $cache->addTableToCache("banlist", 120, true);
        $cache->addTableToCache("botconfig", 120, true);
        $cache->addTableToCache("noticenotecard", 120, true);
        $cache->addTableToCache("notice", 120, true, true);
        $cache->addTableToCache("package", 120, true, true);
        $cache->addTableToCache("region", 120, true);
        $cache->addTableToCache("reseller", 120, true, true);
        $cache->addTableToCache("server", 120, true, true);
        $cache->addTableToCache("servertypes", 120, true);
        $cache->addTableToCache("slconfig", 120, true, true);
        $cache->addTableToCache("template", 120, true, true);
        $cache->addTableToCache("textureconfig", 120, true, true);
        $cache->addTableToCache("timezones", 120, true, true);
        $cache->addTableToCache("treevender", 120, true);
        $cache->addTableToCache("treevenderpackages", 120, true);
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
