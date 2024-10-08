<?php

namespace App\Endpoint\View\Login;

class DefaultView extends View
{
    public function process(): void
    {
        $this->setSwapTag("html_title", "Login");
        $this->output->addSwapTagString("why_logged_out", $this->siteConfig->getSession()->getWhyLoggedOut());
        $this->output->addSwapTagString(
            "page_content",
            file_get_contents($this->siteConfig->getRootFolder() . "/App/Endpoint/View/Login/login.layout")
        );
    }
}
