<?php

namespace YAPF\Bootstrap\ConfigBox;

use YAPF\Framework\Config\SimpleConfig;

class BootstrapConfigBox extends SimpleConfig
{
    // url switchs
    protected string $page = "";
    protected string $module = "";
    protected string $option = "";
    protected string $area = "";
    protected array $pageUrlBits = [];

    // site flags
    protected string $html_title_after = "";
    protected string $SITE_URL = "";
    protected string $html_title = "";

    // Folders
    protected string $rootfolder = "../";
    protected string $deepFolder = "../../";

    // config Flags
    protected array $flags = [];

    public function __construct()
    {
        parent::__construct();
        $this->setFlag("SITE_NAME", "bootstrap enabled");
        $this->setFlag("SITE_URL", "http://localhost/");
        $this->loadURL();
        $this->loadFromDocker();
    }

    public function getSiteName(): string
    {
        return $this->getFlag("SITE_NAME");
    }

    public function getSiteURL(): string
    {
        return $this->getFlag("SITE_URL");
    }

    protected function loadFromDocker(): void
    {
        if (getenv('SITE_CACHE_ENABLED') !== false) {
            if (getenv('SITE_CACHE_ENABLED') == "true") {
                $this->configCacheRedisTCP(getenv("SITE_CACHE_REDIS_HOST"));
            }
            $this->dockerConfigLocked = true; // disable all config functions
            $this->setupCache();
            $this->setupCacheTables();
            $this->startCache();
        }
    }

    /*
        Tables to enable with cache
    */
    protected function setupCacheTables(): void
    {
    }

    /*
        Folder control
    */
    public function &getRootFolder(): string
    {
        return $this->rootfolder;
    }

    public function &getDeepFolder(): string
    {
        return $this->deepFolder;
    }

    public function setFolders(string $rootFolder, string $deepFolder): void
    {
        $this->rootfolder = $rootFolder;
        $this->deepFolder = $deepFolder;
    }

    /*
        Flag control
    */
    public function setFlag(string $envName, ?string $defaultValue): void
    {
        $allowSet = true;
        if (array_key_exists($envName, $this->flags) == true) {
            $allowSet = !$this->dockerConfigLocked;
        }
        if ($allowSet == false) {
            return;
        }
        if (getenv($envName) !== false) {
            $this->flags[$envName] = getenv($envName);
            return;
        }
        $this->flags[$envName] = $defaultValue;
    }

    public function getFlag(string $flagName): ?string
    {
        if (array_key_exists($flagName, $this->flags) == false) {
            return null;
        }
        return $this->flags[$flagName];
    }

    /*
        URL loading
    */

    public function &getPage(): string
    {
        return $this->page;
    }
    public function &getModule(): string
    {
        return $this->module;
    }
    public function &getOption(): string
    {
        return $this->option;
    }
    public function &getArea(): string
    {
        return $this->area;
    }

    public function setModule(string $module): void
    {
        $this->module = ucfirst($module);
    }

    public function setArea(string $area): void
    {
        $this->area = ucfirst($area);
    }

    protected function loadURL(string $process = null): void
    {
        $this->module = "";
        $this->area = "";
        $this->page = "";
        $this->option = "";

        if ($process == null) {
            if (array_key_exists("REQUEST_URI", $_SERVER) == true) {
                $process = $_SERVER['REQUEST_URI'];
            }
        }
        if ($process == null) {
            return;
        }
        $uri_parts = explode('?', $process, 2);
        $bits = array_values(array_diff(explode("/", $uri_parts[0]), [""]));
        if (count($bits) > 0) {
            if (strpos($bits[0], "php") !== false) {
                array_shift($bits);
            }
        }
        if (count($bits) == 1) {
            $this->module = urldecode($bits[0]);
        } elseif (count($bits) >= 2) {
            if (count($bits) >= 1) {
                $this->module = $bits[0 ];
            }
            if (count($bits) >= 2) {
                $this->area = $bits[1];
            }
            if (count($bits) >= 3) {
                $this->page = $bits[2];
            }
            if (count($bits) >= 4) {
                $this->option = $bits[3];
            }
        }
        $this->pageUrlBits = $bits;

        $this->module = ucfirst($this->module);
        $this->area = ucfirst($this->area);
        $this->page = ucfirst($this->page);
        $this->option = ucfirst($this->option);

        if ($this->page == "") {
            $this->page = "0";
        }
    }
}
