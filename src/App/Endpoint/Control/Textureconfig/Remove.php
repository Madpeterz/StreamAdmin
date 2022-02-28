<?php

namespace App\Endpoint\Control\Textureconfig;

use App\Models\Textureconfig;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {

        $accept = $this->post("accept");
        $this->setSwapTag("redirect", "textureconfig");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "textureconfig/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->siteConfig->getPage()) == false) {
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
