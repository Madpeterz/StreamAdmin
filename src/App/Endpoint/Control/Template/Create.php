<?php

namespace App\Endpoints\Control\Template;

use App\Models\Template;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $name = $input->postFilter("name");
        $detail = $input->postFilter("detail");
        $notecarddetail = $input->postFilter("notecarddetail");
        if (strlen($name) < 5) {
            $this->output->setSwapTagString("message", "Name length must be 5 or longer");
            return;
        }
        if (strlen($name) > 30) {
            $this->output->setSwapTagString("message", "Name length must be 30 or less");
            return;
        }
        if (strlen($detail) < 5) {
            $this->output->setSwapTagString("message", "template length must be 5 or more");
            return;
        }
        if (strlen($detail) > 800) {
            $this->output->setSwapTagString("message", "template length must be 800 or less");
            return;
        }
        if (strlen($notecarddetail) < 5) {
            $this->output->setSwapTagString("message", "Notecard template length must be 5 or more");
            return;
        }
        $template = new Template();
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecarddetail($notecarddetail);
        $create_status = $template->createEntry();
        if ($create_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf(
                    "Unable to create Template: %1\$s",
                    $create_status["message"]
                )
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Template created");
        $this->output->setSwapTagString("redirect", "template");
    }
}
