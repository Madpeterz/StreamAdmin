<?php

namespace App\MediaServer\Logic;

use App\R7\Model\Apis;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;

class ApiLogicProcess
{
    protected $status = false;
    protected $whyFailed = "";
    protected $apiServerlogicReply = [];
    protected $currentStep = "";
    protected ?Stream $stream = null;
    protected ?Server $server = null;
    protected ?Rental $rental = null;
    protected ?Apis $api = null;
    protected bool $noApiAction = true;
    protected array $steps = [];
    public function __construct(string $setCurrentStep = "")
    {
        global $stream, $rental, $server;
        $this->stream = $stream;
        $this->server = $server;
        $this->rental = $rental;
        $this->currentStep = $setCurrentStep;
        $this->whyFailed = "Starting process: " . $setCurrentStep;
        $this->process();
    }
    /**
     * getApiServerLogicReply
     * @return mixed[] [status => bool, message=>string,reply=>array]
     */
    public function getApiServerLogicReply(): array
    {
        return ["status" => $this->status,"message" => $this->whyFailed,"reply" => $this->apiServerlogicReply];
    }
    public function getGlobalWhyfailed(): string
    {
        global $why_failed;
        if ($why_failed == null) {
            return "unknown";
        }
        return $why_failed;
    }
    public function getNoAction(): bool
    {
        return $this->noApiAction;
    }
    protected function setupServer(): void
    {
        $this->whyFailed = "Setting up server";
        if ($this->server != null) {
            return;
        }
        if ($this->stream == null) {
            return;
        }
        $this->server = new Server();
        $this->server->loadID($this->stream->getServerLink());
    }
    protected function setupRental(): void
    {
        $this->whyFailed = "Setting up rental";
        if ($this->rental != null) {
            return;
        }
        if ($this->stream == null) {
            return;
        }
        $this->rental = new Rental();
        $this->rental->loadByField("StreamLink", $this->stream->getId());
        if ($this->rental->isLoaded() == false) {
            $this->rental = null;
        }
    }
    protected function getStepAction(): string
    {
        if (array_key_exists($this->currentStep, $this->steps) == true) {
            return $this->steps[$this->currentStep];
        } else {
            return "none";
        }
    }
    protected function processLoop(): void
    {
        $this->whyFailed = "in process loop";
        $this->currentStep = $this->getStepAction();
        if ($this->currentStep == "none") {
            $this->status = true;
            $this->whyFailed = "exited current step is: none";
            return;
        }
        $hasApiStep = true;
        if ($this->currentStep != "core_send_details") {
            $hasApiStep = false;
            $getName = "get" . ucfirst($this->currentStep);
            if ($this->api->$getName() == 0) {
                $this->whyFailed = "Api " . $this->api->getName() . " does not support: " . $getName;
                return;
            }

            if ($this->server->$getName() == 0) {
                $this->whyFailed = "Server does not support: " . $getName;
                return;
            }
        }
        $this->status = true;
        $this->whyFailed = "Processing API server logic please check there";
        $this->noApiAction = false;
        $this->apiServerlogicReply = "Starting";
        global $why_failed;
        $this->apiServerlogicReply = createPendingApiRequest(
            $this->server,
            $this->stream,
            $this->rental,
            $this->currentStep,
            "error: %1\$s %2\$s",
            true
        );
        $this->whyFailed = $why_failed;
        $this->status = $this->apiServerlogicReply;
    }
    protected function process(): void
    {
        $this->setupServer();
        $this->setupRental();
        if ($this->server == null) {
            $this->status = false;
            $this->whyFailed = "Server object not loaded / Unable to load server";
            return;
        }
        if ($this->server->isLoaded() == false) {
            $this->status = false;
            $this->whyFailed = "Unable to load a vaild server";
            return;
        }
        $this->api = new Apis();
        if ($this->api->loadID($this->server->getApiLink()) == false) {
            $this->status = false;
            $this->whyFailed = "Unable to load API controler";
            return;
        }
        if ($this->api->getId() <= 1) {
            $this->status = true;
            $this->whyFailed = "No api usage needed";
            return;
        }
        $this->processLoop();
    }
}
