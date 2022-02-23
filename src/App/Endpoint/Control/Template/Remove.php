<?php

namespace App\Endpoint\Control\Template;

use App\Models\Template;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {

        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "template");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "template/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        $template = new Template();
        if ($template->loadID($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find template");
            return;
        }
        $remove_status = $template->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf(
                    "Unable to remove template: %1\$s",
                    $remove_status["message"]
                )
            );
            return;
        }
        $this->ok("Template removed");
    }
}
