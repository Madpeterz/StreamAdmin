<?php

namespace App\MediaServer\Logic;

use App\Helpers\PendingAPI;
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
    }

    public function getnoApiAction(): bool
    {
        return $this->noApiAction;
    }

    public function setServer(Server $server): void
    {
        $this->server = $server;
    }

    public function setStream(Stream $stream): void
    {
        $this->stream = $stream;
    }

    public function setRental(Rental $rental): void
    {
        $this->rental = $rental;
    }

    public function autoLoad(): void
    {
        $this->setupServer();
        $this->setupRental();
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
        $this->rental->loadByField("streamLink", $this->stream->getId());
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
    protected function nextStep(): void
    {
        // add next step
        $this->noApiAction = true;
        $this->whyFailed = "in process loop";
        $this->currentStep = $this->getStepAction();
        if ($this->currentStep == "none") {
            $this->status = true;
            $this->whyFailed = "none";
            return;
        }
        if ($this->currentStep != "core_send_details") {
            $getName = "get" . ucfirst($this->currentStep);
            if ($this->api->$getName() == false) {
                $this->whyFailed = "Api " . $this->api->getName() . " does not support: " . $getName;
                return;
            }
            if ($this->server->$getName() == false) {
                $this->whyFailed = "Server does not support: " . $getName;
                return;
            }
        }
        $this->noApiAction = false;
        $this->status = true;
        $this->whyFailed = "Processing API server logic please check there";
        $this->apiServerlogicReply = "Starting";
        $pending = new PendingAPI();
        $pending->setStream($this->stream);
        $pending->setServer($this->server);
        $pending->setRental($this->rental);
        $reply = $pending->create($this->currentStep);
        $this->whyFailed = $reply["message"];
        $this->status = $reply["status"];
    }

    /**
     * createNextApiRequest
     * @return mixed[] [status => bool, message=>string]
     */
    public function createNextApiRequest(): array
    {
        $this->autoLoad();
        $this->noApiAction = true;
        if ($this->server == null) {
            $this->status = false;
            $this->whyFailed = "Server object not loaded / Unable to load server";
            return ["status" => $this->status,"message" => $this->whyFailed];
        }
        if ($this->server->isLoaded() == false) {
            $this->status = false;
            $this->whyFailed = "Unable to load a vaild server";
            return ["status" => $this->status,"message" => $this->whyFailed];
        }
        $this->api = new Apis();
        if ($this->api->loadID($this->server->getApiLink()) == false) {
            $this->status = false;
            $this->whyFailed = "Unable to load API controler";
            return ["status" => $this->status,"message" => $this->whyFailed];
        }
        if ($this->api->getId() <= 1) {
            $this->status = true;
            $this->whyFailed = "No api usage needed";
        }
        $this->nextStep();
        return ["status" => $this->status,"message" => $this->whyFailed];
    }
}
