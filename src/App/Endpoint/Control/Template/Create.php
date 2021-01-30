<?php

namespace App\Endpoint\Control\Template;

use App\R7\Model\Template;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
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
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecardDetail($notecardDetail);
        $create_status = $template->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to create Template: %1\$s",
                    $create_status["message"]
                )
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Template created");
        $this->setSwapTag("redirect", "template");
    }
}
