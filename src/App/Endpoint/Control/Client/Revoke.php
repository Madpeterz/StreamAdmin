<?php

namespace App\Endpoints\Control\Client;

use App\Models\ApirequestsSet;
use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Revoke extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rental = new Rental();
        $api_requests = new ApirequestsSet();
        $stream = new Stream();
        $server = new Server();
        $package = new Package();
        $avatar = new Avatar();

        $accept = $input->postFilter("accept");
        $status = false;
        $redirect = "client/manage/" . $this->page . "";
        $this->output->setSwapTagString("redirect", null);
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            return;
        }

        if ($rental->loadByField("rental_uid", $this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find client");
            return;
        }

        if ($api_requests->loadByField("rentallink", $rental->getId()) == false) {
            $this->output->setSwapTagString(
                "message",
                "Unable to check for pending api requests attached to the client"
            );
            return;
        }

        if ($api_requests->getCount() > 0) {
            $this->output->setSwapTagString(
                "message",
                sprintf("There are %1\$s pending api requests attached to the client", $api_requests->getCount())
            );
            return;
        }

        if ($stream->loadID($rental->getStreamlink()) == true) {
            $this->output->setSwapTagString("message", "Unable to find attached stream");
            return;
        }

        if ($server->loadID($stream->getServerlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load server");
            return;
        }

        $stream->setRentallink(null);
        $stream->setNeedwork(1);
        $update_status = $stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to mark stream as needs work");
            return;
        }

        if ($package->loadID($rental->getPackagelink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load package");
            return;
        }
        if ($avatar->loadID($rental->getAvatarlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load avatar");
            return;
        }

        $remove_status = $rental->removeEntry();
        $all_ok = $remove_status["status"];
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove client: %1\$s", $remove_status["message"])
            );
            return;
        }

        $rental = null;
        $api_serverlogic_reply = false;
        include "shared/media_server_apis/logic/revoke.php";
        if ($api_serverlogic_reply == false) {
            $this->output->setSwapTagString("message", $why_failed);
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("redirect", "client");
        $this->output->setSwapTagString("message", "Client rental revoked");
    }
}
