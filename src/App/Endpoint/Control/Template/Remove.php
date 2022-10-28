<?php

namespace App\Endpoint\Control\Template;

use App\Models\Template;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {

        $accept = $this->input->post("accept")->asString();
        $this->setSwapTag("redirect", "template");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "template/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        $template = new Template();
        if ($template->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find template");
            return;
        }
        $remove_status = $template->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to remove template: %1\$s",
                    $remove_status->message
                )
            );
            return;
        }
        $this->ok("Template removed");
    }
}
