<?php

namespace App\Switchboard;

use App\Template\SecondlifeAjax;

class CronTab extends ConfigEnabled
{
    public function __construct()
    {
        global $system;
        $this->siteConfig = $system;
        $_POST["version"] = $this->siteConfig->getSlConfig()->getDbVersion();

        parent::__construct();
    }
    protected string $targetEndpoint = "Cronjob";
    protected function accessChecks(): bool
    {
        $options = $this->getOpts();
        if (array_key_exists("t", $options) == false) {
            return false;
        }
        if (array_key_exists("d", $options) == false) {
            return false;
        }
        $delay = intval($options["d"]);
        if (($delay < 1) || ($delay > 55)) {
            return false;
        }
        $objecttaskid = 0;
        if ($options["t"] == "Botcommandq") {
            $objecttaskid = 1;
        } elseif ($options["t"] == "Detailsserver") {
            $objecttaskid = 2;
        } elseif ($options["t"] == "Dynamicnotecards") {
            $objecttaskid = 3;
        }
        $_POST["objectuuid"] = "" . $objecttaskid . "0000000-0000-0000-0000-000000000000";
        $_POST["ownername"] = "cron";
        $_POST["ownerkey"] = "cron";
        $_POST["pos"] = "0,0,0";
        $_POST["regionname"] = "cron";
        $_POST["objecttype"] = "cron";
        $_POST["mode"] = "cron";
        $_POSt["objectname"] = $options["t"];
        sleep($delay);
        $this->loadingModule = "Tasks";
        $this->loadingArea = $options["t"];
        return true;
    }
    protected function loadPage(): void
    {
        $this->loadingModule = $this->config->getModule();
        $this->loadingArea = $this->config->getArea();

        if ($this->notSet($this->loadingModule) == true) {
            $this->loadingModule = $this->defaultModule;
        }

        if ($this->accessChecks() == false) {
            $this->addError("failed checks");
            http_response_code(400);
            print json_encode([
                "status" => "0",
                "message" => "badly formated request",
            ]);
            return;
        }
        if (in_array($this->loadingArea, ["", "*"]) == true) {
            $this->loadingArea = $this->defaultArea;
        }
        $use_class = $this->findMasterClass();
        if ($use_class === null) {
            $this->addError("Unsupported request");
            print json_encode([
                "status" => "0",
                "message" => "[" . $this->loadingModule . " | "
                    . $this->loadingArea . " | " . $this->config->getPage() . "] Unsupported",
            ]);
            http_response_code(501);
            return;
        }
        /**
         * @var SecondlifeAjax
         */
        $this->loadedObject = new $use_class();
        $this->loadedObject->setOwnerOverride(true);
        if ($this->loadedObject->getLoadOk() == true) {
            $this->finalize();
        }
        $this->loadedObject->getoutput();
        $statussql = $this->loadedObject->getOutputObject()->getSwapTagBool("status");

        if (($statussql === false) || ($statussql === null)) {
            $this->config->getCacheWorker()?->shutdown(false);
            $this->config->getSQL()->flagError();
            return;
        }
        $this->config->getCacheWorker()?->shutdown(true);
        $this->config->shutdown();
    }
}
