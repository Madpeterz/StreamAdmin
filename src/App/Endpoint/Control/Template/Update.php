<?php

namespace App\Endpoints\Control\Template;

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
        if ($template->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find template");
            return;
        }
        $template->setName($name);
        $template->setDetail($detail);
        $template->setNotecarddetail($notecarddetail);
        $update_status = $template->updateEntry();
        if ($update_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf(
                    "Unable to update Template: %1\$s",
                    $update_status["message"]
                )
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Template updated");
        $this->output->setSwapTagString("redirect", "template");
    }
}
