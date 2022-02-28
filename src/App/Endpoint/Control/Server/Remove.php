<?php

namespace App\Endpoint\Control\Server;

use App\Models\Server;
use App\Models\Sets\StreamSet;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {

        $server = new Server();
        $stream_set = new StreamSet();

        $accept = $this->post("accept")->asString();
        $this->setSwapTag("redirect", "server");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "server/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        if ($server->loadID($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find server");
            return;
        }
        $load_status = $stream_set->loadByServerLink($server->getId());
        if ($load_status["status"] == false) {
            $this->failed("Unable to check if the server is being used by a stream");
            return;
        }
        if ($stream_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove server it is currently being used by: %1\$s stream('s)",
                    $stream_set->getCount()
                )
            );
            return;
        }
        $remove_status = $server->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf(
                    "Unable to remove server: %1\$s",
                    $remove_status["message"]
                )
            );
            return;
        }
        $this->ok("Server removed");
    }
}
