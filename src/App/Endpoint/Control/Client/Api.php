<?php

namespace App\Endpoint\Control\Client;

use App\Models\Rental;
use App\Models\Stream;
use App\Template\ViewAjax;
use serverapi_helper;

class Api extends ViewAjax
{
    public function process(): void
    {
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to load rental");
            return;
        }
        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server_api_helper = new serverapi_helper($stream);
        $functionname = "api_" . $this->option . "";
        if (method_exists($server_api_helper, $functionname) == false) {
            $this->setSwapTag("message", "Unable to load api: " . $functionname);
            return;
        }
        $status = $server_api_helper->$functionname();
        $this->setSwapTag("status", $status);
        $message = "No message from api helper";
        if (is_string($server_api_helper->getMessage()) == true) {
            $message = $server_api_helper->getMessage();
        }
        if ($status == false) {
            $this->setSwapTag("message", sprintf("API/Failed => %1\$s", $message));
            return;
        }
        $this->setSwapTag("message", sprintf("API/Ok => %1\$s", $message));
    }
}
