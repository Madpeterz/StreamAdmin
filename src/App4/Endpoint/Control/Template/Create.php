<?php

namespace App\Endpoint\Control\Template;

use App\Models\Template;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $name = $this->input->post("name")->checkStringLength(5, 30)->asString();
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $detail = $this->input->post("detail")->checkStringLength(5, 800)->asString();
        if ($detail == null) {
            $this->failed("Template failed:" . $this->input->getWhyFailed());
            return;
        }
        $notecardDetail = $this->input->post("notecardDetail")->checkStringLength(5, 5000)->asString();
        if ($notecardDetail == null) {
            $this->failed("Template failed:" . $this->input->getWhyFailed());
            return;
        }
        $template = new Template();
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecardDetail($notecardDetail);
        $create_status = $template->createEntry();
        if ($create_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to create Template: %1\$s",
                    $create_status->message
                )
            );
            return;
        }
        $this->ok("Template created");
        $this->setSwapTag("redirect", "template");
    }
}
