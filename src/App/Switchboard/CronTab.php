<?php

namespace App\Switchboard;

use App\Models\Avatar;
use App\Template\SecondlifeAjax;

class CronTab extends ConfigEnabled
{
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
        $ownerAv = new Avatar();
        $ownerAv->loadId($this->siteConfig->getSlConfig()->getOwnerAvatarLink());
        if ($ownerAv->isLoaded() == false) {
            return false;
        }
        $_POST["version"] = $this->siteConfig->getSlConfig()->getDbVersion();
        $_POST["objectuuid"] = "" . $objecttaskid . "0000000-0000-0000-0000-000000000000";
        $_POST["ownername"] = $ownerAv->getAvatarName();
        $_POST["ownerkey"] = $ownerAv->getAvatarUUID();
        $_POST["pos"] = "0,0,0";
        $_POST["regionname"] = "cron";
        $_POST["objecttype"] = "cron";
        $_POST["mode"] = "cron";
        $_POST["objectname"] = $options["t"];
        $_POST["unixtime"] = time();
        $required_sl = [
            "Tasks",
            $options["t"],
            $_POST["unixtime"],
            $_POST["version"],
            $_POST["mode"],
            $_POST["objectuuid"],
            $_POST["regionname"],
            $_POST["ownerkey"],
            $_POST["ownername"],
            $_POST["pos"],
            $_POST["objectname"],
            $_POST["objecttype"],
            $this->siteConfig->getSlConfig()->getSlLinkCode(),
        ];
        $_POST["hash"] = sha1(implode("", $required_sl));
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
