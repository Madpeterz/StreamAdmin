<?php

namespace App\Endpoint\Control\Template;

use App\Models\Template;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $name = $input->postFilter("name");
        $detail = $input->postFilter("detail");
        $notecardDetail = $input->postFilter("notecardDetail");
        if (strlen($name) < 5) {
            $this->setSwapTag("message", "Name length must be 5 or longer");
            return;
        }
        if (strlen($name) > 30) {
            $this->setSwapTag("message", "Name length must be 30 or less");
            return;
        }
        if (strlen($detail) < 5) {
            $this->setSwapTag("message", "template length must be 5 or more");
            return;
        }
        if (strlen($detail) > 800) {
            $this->setSwapTag("message", "template length must be 800 or less");
            return;
        }
        if (strlen($notecardDetail) < 5) {
            $this->setSwapTag("message", "Notecard template length must be 5 or more");
            return;
        }
        $template = new Template();
        if ($template->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find template");
            return;
        }
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecardDetail($notecardDetail);
        $update_status = $template->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to update Template: %1\$s",
                    $update_status["message"]
                )
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Template updated");
        $this->setSwapTag("redirect", "template");
    }
}
