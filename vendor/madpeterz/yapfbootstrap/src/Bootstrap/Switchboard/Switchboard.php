<?php

namespace YAPF\Bootstrap\Switchboard;

use YAPF\Bootstrap\ConfigBox\BootstrapConfigBox;
use YAPF\Bootstrap\Template\View;
use YAPF\Core\ErrorControl\ErrorLogging;

abstract class Switchboard extends ErrorLogging
{
    protected BootstrapConfigBox $config;

    protected string $targetEndpoint = "";
    protected ?View $loadedObject;

    protected string $loadingModule = "";
    protected string $loadingArea = "";

    public function __construct()
    {
        global $system;
        $this->config = $system;
        $this->loadPage();
    }

    protected function accessChecks(): bool
    {
        return true;
    }

    protected function notSet(?string $input): bool
    {
        if (($input === "") || ($input === null)) {
            return true;
        }
        return false;
    }

    protected function findMasterClass(): ?string
    {
        $routes = [
            [$this->loadingArea],
            [],
            [$this->loadingArea,$this->config->getPage()],
            [$this->loadingArea,$this->config->getPage(),$this->config->getOption()],
            ["DefaultView"],
        ];
        foreach ($routes as $route) {
            $bits = array_merge(["App","Endpoint",$this->targetEndpoint,$this->loadingModule], $route);
            $use_class = "\\" . implode("\\", $bits);
            if (class_exists($use_class) == false) {
                continue;
            }
            return $use_class;
        }
        return null;
    }

    protected function loadPage(): void
    {
        $this->loadingModule = $this->config->getModule();
        $this->loadingArea = $this->config->getArea();

        if ($this->notSet($this->loadingModule) == true) {
            $this->loadingModule = "Home";
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
        if (in_array($this->loadingArea, ["","*"]) == true) {
            $this->loadingArea = "DefaultView";
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

        $this->loadedObject = new $use_class();
        if ($this->loadedObject->getLoadOk() == true) {
            $this->fininalize();
        }
        $this->loadedObject->getoutput();
        $statussql = $this->loadedObject->getOutputObject()->getSwapTagBool("status");
        if (($statussql === false) || ($statussql === null)) {
            $this->config->getSQL()->flagError();
        }
    }

    protected function fininalize(): void
    {
        $this->loadedObject->getOutputObject()->setSwapTag("module", $this->loadingModule);
        $this->loadedObject->getOutputObject()->setSwapTag("area", $this->loadingArea);
        $this->loadedObject->getOutputObject()->setSwapTag("cache_status", "N/A");
        if ($this->config->getCacheDriver() != null) {
            $this->loadedObject->getOutputObject()->setSwapTag("cache_status", json_encode($this->config->getCacheDriver()->getStatusCounters()));
        }
        $this->loadedObject->process();
    }
}
