<?php

namespace App\Endpoint\View\Login;

class Logout extends View
{
    public function process(): void
    {
        $this->session->endSession();
        $this->output->setSwapTag(
            "page_content",
            "Logged out"
        );
        $this->output->redirect("here");
    }
}
