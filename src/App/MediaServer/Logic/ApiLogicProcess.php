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
    public function getNoAction(): bool
    {
        return $this->noApiAction;
    }
    protected function setupServer(): void
    {
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
        if (array_key_exists($this->current_step, $this->steps) == true) {
            return $this->steps[$this->current_step];
        } else {
            return "none";
        }
    }
    protected function processLoop(): void
    {
        $this->current_step = $this->getStepAction();
        if ($this->current_step == "none") {
            return;
        }
        $hasApiStep = true;
        if ($this->current_step != "core_send_details") {
            $hasApiStep = false;
            $getName = "get" . ucfirst($this->current_step);
            if (($this->api->$getName() == 1) && ($this->server->$getName() == 1)) {
                $hasApiStep = true;
            }
        }
        if ($hasApiStep == false) {
            return;
        }
        $this->status = true;
        $this->whyFailed = "Processing API server logic please check there";
        $this->noApiAction = false;
        $this->api_serverlogic_reply = createPendingApiRequest(
            $this->server,
            $this->stream,
            $this->rental,
            $this->current_step,
            "error: %1\$s %2\$s",
            true
        );
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
