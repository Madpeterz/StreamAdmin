<?php

namespace App\Endpoint\Control\Textureconfig;

use App\Models\Textureconfig;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {
        $accept = $this->input->post("accept")->asString();
        $this->setSwapTag("redirect", "textureconfig");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "textureconfig/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find texture pack");
            return;
        }
        $textureid = $textureconfig->getId();
        $texturename = $textureconfig->getName();
        $remove_status = $textureconfig->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(
                sprintf("Unable to remove texture pack: %1\$s", $remove_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Texture pack removed");
        $this->createAuditLog($textureid, "---", $texturename);
    }
}
