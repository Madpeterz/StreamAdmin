<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

use App\Models\Apirequests;
use App\Models\Stream;
use App\Template\SecondlifeAjax;
use serverapi_helper;

abstract class CallApi extends SecondlifeAjax
{
    protected ?Apirequests $targetEvent;
    protected $functionname = "";
    protected $logic_step = "";

    public function attachEvent(Apirequests $request): void
    {
        $this->targetEvent = $request;
    }
    public function process(): void
    {
        $this->configEvent();
        $stream = new Stream();
        $status = false;
        $message = "Started call_api";
        $current_step = $this->functionname;
        $retry = false;
        if ($stream->loadID($this->targetEvent->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server_api_helper = new serverapi_helper($stream);
        if (method_exists($server_api_helper, $current_step) == false) {
            $this->setSwapTag(
                "message",
                "Unable to run api function: " . $this->functionname . " because: its missing"
            );
            return;
        }
        $status = $server_api_helper->$this->functionname();
        $message = $server_api_helper->getMessage();
        if ($status == false) {
            $this->setSwapTag(
                "message",
                "API call " . $this->functionname . " failed with: " . $message
            );
            return;
        }
        if ($retry == true) {
            $this->setSwapTag("status", "true");
            $this->setSwapTag("message", "retry");
            return;
        }
        $remove_status = $this->targetEvent->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag("message", "Unable to remove old api request");
            return;
        }
        $why_failed = "";
        if ($this->logic_step == "opt") {
            $this->setSwapTag("status", "true");
            $this->setSwapTag("message", "ok");
            return;
        }
        include "shared/media_server_apis/logic/" . $logic_step . ".php";
        $status = $api_serverlogic_reply;
        if ($status == true) {
            $message = "ok reply from " . $logic_step . " - " . $functionname . "";
        } else {
            $message = $why_failed;
        }
    }
    protected function configEvent(): void
    {
    }
}
