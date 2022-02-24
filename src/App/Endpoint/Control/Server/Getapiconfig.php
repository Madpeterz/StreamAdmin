<?php

namespace App\Endpoint\Control\Server;

use App\Models\Apis;
use App\Framework\ViewAjax;

class Getapiconfig extends ViewAjax
{
    public function process(): void
    {

        $apiLink = $this->input->post("apiLink", "integer");
        $api = new Apis();
        if ($apiLink == 0) {
            $this->failed("Invaild API selected");
            return;
        }
        if ($api->loadID($apiLink) == false) {
            $this->failed("Unknown API selected");
            return;
        }
        foreach ($api->getFields() as $apifield) {
            $getter = "get" . ucfirst($apifield);
            $this->setSwapTag($apifield, $api->$getter());
        }
        $this->setSwapTag("update_api_flags", "true");
        $this->ok("API config loaded");
    }
}
