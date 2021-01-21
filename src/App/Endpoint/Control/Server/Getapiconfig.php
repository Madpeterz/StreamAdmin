<?php

namespace App\Endpoint\Control\Server;

use App\Models\Apis;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Getapiconfig extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $apiLink = $input->postFilter("apiLink", "integer");
        $api = new Apis();
        $status = false;
        if ($apiLink == 0) {
            $this->setSwapTag("message", "Invaild API selected");
            return;
        }
        if ($api->loadID($apiLink) == false) {
            $this->setSwapTag("message", "Unknown API selected");
            return;
        }
        foreach ($api->getFields() as $apifield) {
            $getter = "get" . ucfirst($apifield);
            $this->setSwapTag($apifield, $api->$getter());
        }
        $this->setSwapTag("update_api_flags", "true");
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "API config loaded");
    }
}
