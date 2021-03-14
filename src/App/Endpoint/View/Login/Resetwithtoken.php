<?php

namespace App\Endpoint\View\Login;

class Resetwithtoken extends View
{
    public function process(): void
    {
        $this->setSwapTag("html_title", "Recover password");
        $this->output->addSwapTagString(
            "page_content",
            file_get_contents("" . ROOTFOLDER . "/App/Endpoint/View/Login/passwordrecover.layout")
        );
    }
}
