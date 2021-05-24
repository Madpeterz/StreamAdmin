<?php

namespace App\Endpoint\Control\Client;

use App\MediaServer\Logic\ApiLogicRevoke;
use App\R7\Set\ApirequestsSet;
use App\R7\Model\Avatar;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Revoke extends ViewAjax
{
    protected InputFilter $input;
    protected Rental $rental;
    protected ApirequestsSet $api_requests;
    protected Stream $stream;
    protected Server $server;
    protected Package $package;
    protected Avatar $avatar;

    public function process(): void
    {
        $this->input = new InputFilter();
        $this->rental = new Rental();
        $this->api_requests = new ApirequestsSet();
        $this->stream = new Stream();
        $this->server = new Server();
        $this->package = new Package();
        $this->avatar = new Avatar();

        $accept = $this->input->postFilter("accept");
        $this->setSwapTag("redirect", null);
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            return;
        }
        if ($this->load() == false) {
            return;
        }
        if ($this->revoke() == false) {
            return;
        }
        $this->processAPI();
    }

    protected function load(): bool
    {
        if ($this->rental->loadByField("rentalUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find client");
            return false;
        }

        if ($this->api_requests->loadByField("rentalLink", $this->rental->getId()) == false) {
            $this->setSwapTag(
                "message",
                "Unable to check for pending api requests attached to the client"
            );
            return false;
        }

        if ($this->api_requests->getCount() > 0) {
            $this->setSwapTag(
                "message",
                sprintf("There are %1\$s pending api requests attached to the client", $this->api_requests->getCount())
            );
            return false;
        }

        if ($this->stream->loadID($this->rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to find attached stream");
            return false;
        }

        if ($this->server->loadID($this->stream->getServerLink()) == false) {
            $this->setSwapTag("message", "Unable to load server");
            return false;
        }

        if ($this->package->loadID($this->rental->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return false;
        }
        if ($this->avatar->loadID($this->rental->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load avatar");
            return false;
        }
        return true;
    }

    protected function revoke(): bool
    {
        $this->stream->setRentalLink(null);
        $this->stream->setNeedWork(1);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag("message", "Unable to mark stream as needs work");
            return false;
        }

        $remove_status = $this->rental->removeEntry();
        $all_ok = $remove_status["status"];
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove client: %1\$s", $remove_status["message"])
            );
            return false;
        }
        return true;
    }

    protected function processAPI(): void
    {
        $api_serverlogic_reply = true;
        $apilogic = new ApiLogicRevoke();
        $apilogic->setStream($this->stream);
        $apilogic->setServer($this->server);
        $reply = $apilogic->createNextApiRequest();
        if ($reply["status"] == false) {
            $this->setSwapTag("message", $reply["message"]);
            return;
        }

        $this->setSwapTag("status", $reply["status"]);
        if ($api_serverlogic_reply == true) {
            $this->setSwapTag("redirect", "client");
        }
        $this->setSwapTag("message", "Client rental revoked");
    }
}
