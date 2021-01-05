<?php

namespace App\Endpoints\Control\Textureconfig;

use App\Models\Textureconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "textureconfig");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            $this->output->setSwapTagString("redirect", "textureconfig/manage/" . $this->page . "");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find texture pack");
            return;
        }
        $remove_status = $textureconfig->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove texture pack: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "texture pack removed");
    }
}
