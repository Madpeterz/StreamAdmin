<?php

namespace App\Helpers;

use App\R7\Model\Apirequests;
use App\R7\Model\Detail;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;

class PendingAPI
{
    protected ?Server $server = null;
    protected ?Stream $stream = null;
    protected ?Rental $rental = null;

    /**
     * create
     * @return mixed[] [status => bool, noaction => bool message=>string]
     */
    public function create(
        string $eventname
    ): array {
        $this->autoLoad($this->stream);
        $why_failed = "ok";
        if ($this->server == null) {
            return ["status" => false,"message" => "Server is missing and unable to be loaded"];
        }
        if ($eventname == "core_send_details") {
            $detail = new Detail();
            $detail->setRentalLink($this->rental->getId());
            $create_status = $detail->createEntry();
            $status = $create_status["status"];
            if ($status == false) {
                $why_failed = "Failed creating detail:" .
                sprintf("error: %1\$s %2\$s", $eventname, $create_status["message"]);
            }
            return ["status" => $status,"message" => $why_failed];
        }
        $api_request = new Apirequests();
        $api_request->setServerLink($this->server->getId());
        $api_request->setRentalLink(null);
        if ($this->rental != null) {
            $api_request->setRentalLink($this->rental->getId());
        }
        $api_request->setStreamLink($this->stream->getId());
        $api_request->setEventname($eventname);
        $api_request->setMessage("in Q");
        $api_request->setLastAttempt(time());
        $reply = $api_request->createEntry();
        $why_failed = "passed";
        $status = $reply["status"];
        if ($reply["status"] == false) {
            $why_failed = sprintf("error: %1\$s %2\$s", $eventname, $reply["message"]);
            return false;
        }
        return ["status" => $status,"message" => $why_failed];
    }


    public function setServer(?Server $server): void
    {
        $this->server = $server;
    }

    public function setStream(?Stream $stream): void
    {
        $this->stream = $stream;
    }

    public function setRental(?Rental $rental): void
    {
        $this->rental = $rental;
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

    protected function autoLoad(): void
    {
        $this->setupServer();
        $this->setupRental();
    }
}
