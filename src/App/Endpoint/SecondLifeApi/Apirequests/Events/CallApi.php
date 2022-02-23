<?php

namespace App\Endpoint\SecondLifeApi\Apirequests\Events;

use App\Helpers\ServerApi\ServerApiHelper;
use App\MediaServer\Logic\ApiLogicBuy;
use App\MediaServer\Logic\ApiLogicCreate;
use App\MediaServer\Logic\ApiLogicExpire;
use App\MediaServer\Logic\ApiLogicRenew;
use App\MediaServer\Logic\ApiLogicRevoke;
use App\Models\Apirequests;
use App\Models\Stream;
use App\Template\SecondlifeAjax;

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
        $this->setSwapTag("message", "Starting process for: " . $this->functionname . " " . $this->logic_step);
        $stream = new Stream();
        $status = false;
        $message = "Started call_api";
        $current_step = $this->functionname;
        $retry = false;
        if ($stream->loadID($this->targetEvent->getStreamLink()) == false) {
            $this->setSwapTag("message", "Unable to load stream");
            return;
        }
        $server_api_helper = new ServerApiHelper($stream);
        if (method_exists($server_api_helper, $current_step) == false) {
            $this->setSwapTag(
                "message",
                "Unable to run api function: " . $this->functionname . " because: its missing"
            );
            return;
        }
        $call_function = $this->functionname;
        $status = $server_api_helper->$call_function();
        $message = $server_api_helper->getMessage();
        if ($status == false) {
            $this->setSwapTag(
                "message",
                "API call " . $this->functionname . " failed with: " . $message
            );
            return;
        }
        if ($retry == true) {
            $this->setSwapTag("status", true);
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
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "ok");
            return;
        }

        $api_logic_object = null;
        if ($this->logic_step == "revoke") {
            $api_logic_object = new ApiLogicRevoke($current_step);
        } elseif ($this->logic_step == "create") {
            $api_logic_object = new ApiLogicCreate($current_step);
        } elseif ($this->logic_step == "expire") {
            $api_logic_object = new ApiLogicExpire($current_step);
        } elseif ($this->logic_step == "buy") {
            $api_logic_object = new ApiLogicBuy($current_step);
        } elseif ($this->logic_step == "revoke") {
            $api_logic_object = new ApiLogicRevoke($current_step);
        } elseif ($this->logic_step == "renew") {
            $api_logic_object = new ApiLogicRenew($current_step);
        }

        if ($api_logic_object == null) {
            $this->setSwapTag("status", false);
            $this->setSwapTag("message", "Unknown logic controler: " . $this->logic_step);
            return;
        }
        $api_logic_object->setStream($stream);
        $reply = $api_logic_object->createNextApiRequest();
        $this->setSwapTag("status", $reply["status"]);
        $this->setSwapTag("message", $reply["message"]);
    }
    protected function configEvent(): void
    {
    }
}
