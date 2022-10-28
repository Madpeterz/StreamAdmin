<?php

namespace App\Endpoint\Control\Template;

use App\Models\Template;
use App\Template\ControlAjax;

class Update extends ControlAjax
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
        if ($template->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find template");
            return;
        }
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecardDetail($notecardDetail);
        $update_status = $template->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to update Template: %1\$s",
                    $update_status->message
                )
            );
            return;
        }
        $this->ok("Template updated");
        $this->setSwapTag("redirect", "template");
    }
}
