<?php

namespace App\Endpoint\Control\Server;

use App\R7\Model\Apis;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Getapiconfig extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $apiLink = $input->postFilter("apiLink", "integer");
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
