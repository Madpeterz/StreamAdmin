<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\EventsQHelper;
use App\MediaServer\Logic\ApiLogicRevoke;
use App\R7\Set\ApirequestsSet;
use App\R7\Model\Avatar;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\R7\Set\RentalnoticeptoutSet;
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

        $accept = $this->input->postString("accept");
        $this->setSwapTag("redirect", null);
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
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
        if ($this->rental->loadByRentalUid($this->page) == false) {
            $this->failed("Unable to find client");
            return false;
        }
        if ($this->api_requests->loadByRentalLink($this->rental->getId()) == false) {
            $this->failed("Unable to check for pending api requests attached to the client");
            return false;
        }
        if ($this->api_requests->getCount() > 0) {
            $this->failed(sprintf(
                "There are %1\$s pending api requests attached to the client",
                $this->api_requests->getCount()
            ));
            return false;
        }
        if ($this->stream->loadID($this->rental->getStreamLink()) == false) {
            $this->failed("Unable to find attached stream");
            return false;
        }
        if ($this->server->loadID($this->stream->getServerLink()) == false) {
            $this->failed("Unable to load server");
            return false;
        }
        if ($this->package->loadID($this->rental->getPackageLink()) == false) {
            $this->failed("Unable to load package");
            return false;
        }
        if ($this->avatar->loadID($this->rental->getAvatarLink()) == false) {
            $this->failed("Unable to load avatar");
            return false;
        }
        return true;
    }

    protected function revoke(): bool
    {
        $EventsQHelper = new EventsQHelper();
        $EventsQHelper->addToEventQ(
            "RentalEnd",
            $this->package,
            $this->avatar,
            $this->server,
            $this->stream,
            $this->rental
        );
        $this->stream->setRentalLink(null);
        $this->stream->setNeedWork(1);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed("Unable to mark stream as needs work");
            return false;
        }
        $rental_notice_opt_outs = new RentalnoticeptoutSet();
        $load = $rental_notice_opt_outs->loadByRentalLink($this->rental->getId());
        if ($load["status"] == false) {
            $this->failed("Unable to load rental notice opt-outs");
            return false;
        }
        if ($rental_notice_opt_outs->getCount() > 0) {
            $purge = $rental_notice_opt_outs->purgeCollection();
            if ($purge["status"] == false) {
                $this->failed(sprintf("Unable to remove client notice opt-outs: %1\$s", $purge["message"]));
                return false;
            }
        }

        $remove_status = $this->rental->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(sprintf("Unable to remove client: %1\$s", $remove_status["message"]));
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
            $this->failed($reply["message"]);
            return;
        }
        if ($api_serverlogic_reply == true) {
            $this->setSwapTag("redirect", "client");
        }

        $this->ok("Client rental revoked");
    }
}
