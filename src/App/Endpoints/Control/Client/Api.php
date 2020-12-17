<?php

namespace App\Endpoints\Control\Client;

use App\Models\Rental;
use App\Models\Stream;
use App\Template\ViewAjax;
use serverapi_helper;

class Api extends ViewAjax
{
    public function process(): void
    {
        $rental = new Rental();
        if ($rental->loadByField("rental_uid", $this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to load rental");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to load stream");
            return;
        }
        $server_api_helper = new serverapi_helper($stream);
        $functionname = "api_" . $this->option . "";
        if (method_exists($server_api_helper, $functionname) == false) {
            $this->output->setSwapTagString("message", "Unable to load api: " . $functionname);
            return;
        }
        $status = $server_api_helper->$functionname();
        $this->output->setSwapTagString("status", (string)$status);
        $message = "No message from api helper";
        if (is_string($server_api_helper->getMessage()) == true) {
            $message = $server_api_helper->getMessage();
        }
        if ($status == false) {
            $this->output->setSwapTagString("message", sprintf("API/Failed => %1\$s", $message));
            return;
        }
        $this->output->setSwapTagString("message", sprintf("API/Ok => %1\$s", $message));
    }
}
