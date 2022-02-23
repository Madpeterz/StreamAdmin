<?php

namespace App\Endpoint\SecondLifeApi\Apirequests;

use App\Models\Sets\ApirequestsSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("message", "started process");
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "This API is owner only");
            return;
        }

        $where_config = [
            "fields" => ["attempts"],
            "values" => [10],
            "types" => ["i"],
            "matches" => ["<"],
        ];
        $order_config = ["ordering_enabled" => true,"order_field" => "lastAttempt","order_dir" => "DESC"];
        $limits_config = ["page_number" => 0,"max_entrys" => 1];
        $api_requests_set = new ApirequestsSet();

        if ($api_requests_set->loadWithConfig($where_config, $order_config, $limits_config)["status"] == false) {
            $this->setSwapTag("message", "Unable to load next api request");
            return;
        }
        if ($api_requests_set->getCount() == 0) {
            $this->setSwapTag("message", "nowork");
            $this->setSwapTag("status", true);
            return;
        }

        $api_request = $api_requests_set->getFirst();

        if ($api_request->getAttempts() > 10) {
            $api_request->setMessage("given up - please contact support");
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "Giving up on api request!");
            return;
        }

        $api_request->setAttempts($api_request->getAttempts() + 1);
        $api_request->setLastAttempt(time());
        $api_request->setMessage("started processing");
        $save_status = $api_request->updateEntry();
        if ($save_status["status"] == false) {
            $this->setSwapTag("message", "Unable to mark event as processing Obj issue");
            return;
        }


        $targetclass = ucfirst($api_request->getEventname());
        $targetclass = str_replace("_", "", $targetclass);
        $namespace = "\\App\\Endpoint\\SecondLifeApi\\Apirequests\\Events\\";
        $use_class = $namespace . $targetclass;
        if (class_exists($use_class) == false) {
            $this->soft_fail = true;
            $this->setSwapTag("message", "Unable to find event: " . $api_request->getEventname());
            return;
        }
        if ($this->sql->sqlSave(false) == false) {
            $this->setSwapTag("message", "Unable to mark event as processing DB issue");
            return;
        }

        $this->setSwapTag("eventname", $api_request->getEventname());
        $this->setSwapTag("message", "Handing off to api");
        $obj = new $use_class();
        $obj->attachEvent($api_request);
        $obj->process();
        $this->output = $obj->getOutputObject();
    }
}
