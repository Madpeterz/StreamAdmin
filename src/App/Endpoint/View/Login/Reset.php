<?php

namespace App\Endpoint\View\Login;

class Reset extends View
{
    public function process(): void
    {
        $this->setSwapTag("html_title", "Reset password");
        $this->output->addSwapTagString(
            "page_content",
            file_get_contents("" . ROOTFOLDER . "/App/Endpoint/View/Login/reset.layout")
        );
    }
}
