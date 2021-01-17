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
        $this->setSwapTag("redirect", "textureconfig");
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            $this->setSwapTag("redirect", "textureconfig/manage/" . $this->page . "");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find texture pack");
            return;
        }
        $remove_status = $textureconfig->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove texture pack: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "texture pack removed");
    }
}
