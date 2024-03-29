<?php

namespace App\Endpoint\Control\Textureconfig;

use App\R7\Model\Textureconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "textureconfig");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "textureconfig/manage/" . $this->page . "");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->page) == false) {
            $this->failed("Unable to find texture pack");
            return;
        }
        $remove_status = $textureconfig->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf("Unable to remove texture pack: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->ok("Texture pack removed");
    }
}
