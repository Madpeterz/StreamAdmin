<?php

namespace App\Endpoints\Control\Server;

use App\Models\Apis;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Getapiconfig extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $apilink = $input->postFilter("apilink", "integer");
        $api = new Apis();
        $status = false;
        if ($apilink == 0) {
            $this->output->setSwapTagString("message", "Invaild API selected");
            return;
        }
        if ($api->loadID($apilink) == false) {
            $this->output->setSwapTagString("message", "Unknown API selected");
            return;
        }
        foreach ($api->getFields() as $apifield) {
            $getter = "get" . ucfirst($apifield);
            $this->output->setSwapTagString($apifield, $api->$getter());
        }
        $this->output->setSwapTagString("update_api_flags", "true");
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "API config loaded");
    }
}
