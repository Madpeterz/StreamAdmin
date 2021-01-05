<?php

namespace App\Endpoints\Control\Template;

use App\Models\Template;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "template");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            $this->output->setSwapTagString("redirect", "template/manage/" . $this->page . "");
            return;
        }
        $template = new Template();
        if ($template->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find template");
            return;
        }
        $remove_status = $template->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf(
                    "Unable to remove template: %1\$s",
                    $remove_status["message"]
                )
            );
            return;
        }
        $this->output->setSwapTagString("message", "Template removed");
        $this->output->setSwapTagString("status", "true");
    }
}
