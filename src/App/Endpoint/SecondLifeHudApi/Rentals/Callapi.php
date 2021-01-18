<?php

namespace App\Endpoints\SecondLifeHudApi\Rentals;

use App\Models\ApirequestsSet;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class Callapi extends SecondlifeAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $rental_uid = $input->postFilter("uid");
        $request_code = $input->postFilter("apiid");
        $rental = new Rental();
        $accepted_api_calls = ["opt_toggle_autodj","opt_password_reset","opt_autodj_next"];
        if (in_array($request_code, $accepted_api_calls) == false) {
            $this->setSwapTag("message", "Unsupported hud API call");
            return;
        }
        if ($rental->loadByField("rental_uid", $rental_uid) == false) {
            $this->setSwapTag("message", "Unable to load rental");
            return;
        }
        if ($rental->getAvatarlink() != $this->object_owner_avatar->getId()) {
            $this->setSwapTag("message", "Error setting up link");
            return;
        }
        if ($rental->getExpireunixtime() < time()) {
            $this->setSwapTag("message", "Rental is currently expired API calls disabled.");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamlink()) == false) {
            $this->setSwapTag("message", "Unable to load stream linked to rental");
            return;
        }
        $server = new Server();
        if ($server->loadID($stream->getServerlink()) == false) {
            $this->setSwapTag("message", "Unable to load server linked to stream");
            return;
        }
        $pendingapi = new ApirequestsSet();
        $pendingapi->loadByField("streamlink", $rental->getStreamlink());
        if ($pendingapi->getCount() > 0) {
            $this->setSwapTag("message", "There is already a pending API request please wait and try again later");
            return;
        }
        $status = create_pending_api_request(
            $server,
            $stream,
            $rental,
            $request_code,
            "Unable to create event %1\$s because: %2\$s",
            true
        );
        if ($status == false) {
            $this->setSwapTag("message", "Unable to create pending api request");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "ok");
        return;
    }
}
