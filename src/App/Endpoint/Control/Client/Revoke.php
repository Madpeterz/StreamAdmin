<?php

namespace App\Endpoint\Control\Client;

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
        $this->setSwapTag("redirect", null);
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            return;
        }

        if ($rental->loadByField("rentalUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find client");
            return;
        }

        if ($api_requests->loadByField("rentalLink", $rental->getId()) == false) {
            $this->setSwapTag(
                "message",
                "Unable to check for pending api requests attached to the client"
            );
            return;
        }

        if ($api_requests->getCount() > 0) {
            $this->setSwapTag(
                "message",
                sprintf("There are %1\$s pending api requests attached to the client", $api_requests->getCount())
            );
            return;
        }

        if ($stream->loadID($rental->getStreamLink()) == true) {
            $this->setSwapTag("message", "Unable to find attached stream");
            return;
        }

        if ($server->loadID($stream->getServerLink()) == false) {
            $this->setSwapTag("message", "Unable to load server");
            return;
        }

        $stream->setRentalLink(null);
        $stream->setNeedWork(1);
        $update_status = $stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag("message", "Unable to mark stream as needs work");
            return;
        }

        if ($package->loadID($rental->getPackageLink()) == false) {
            $this->setSwapTag("message", "Unable to load package");
            return;
        }
        if ($avatar->loadID($rental->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load avatar");
            return;
        }

        $remove_status = $rental->removeEntry();
        $all_ok = $remove_status["status"];
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove client: %1\$s", $remove_status["message"])
            );
            return;
        }

        $rental = null;
        $api_serverlogic_reply = false;
        include "shared/media_server_apis/logic/revoke.php";
        if ($api_serverlogic_reply == false) {
            $this->setSwapTag("message", $why_failed);
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("redirect", "client");
        $this->setSwapTag("message", "Client rental revoked");
    }
}
