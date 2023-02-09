<?php

namespace App\Endpoint\Control\Server;

use App\Models\Server;
use App\Models\Sets\StreamSet;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {
        $server = new Server();

        $accept = $this->input->post("accept")->asString();
        $this->setSwapTag("redirect", "server");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "server/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        if ($server->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find server");
            return;
        }
        $stream_set = $server->relatedStream();
        if ($stream_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove server it is currently being used by: %1\$s stream('s)",
                    $stream_set->getCount()
                )
            );
            return;
        }
        $serverid = $server->getId();
        $servername = $server->getDomain();
        $remove_status = $server->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to remove server: %1\$s",
                    $remove_status->message
                )
            );
            return;
        }
        $this->createAuditLog($serverid, "---", $servername);
        $this->redirectWithMessage("Server removed");
    }
}
