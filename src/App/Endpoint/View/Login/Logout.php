<?php

namespace App\Endpoint\View\Login;

class Logout extends View
{
    public function process(): void
    {
        $this->siteConfig->getSession()->endSession();
        $this->output->setSwapTag(
            "page_content",
            "Logged out"
        );
        $this->output->redirect("here");
    }
}
