<?php

namespace App\Endpoint\View\Login;

class DefaultView extends View
{
    public function process(): void
    {
        if ($this->area == "reset") {
            $this->resetPassword();
        } elseif ($this->area == "resetwithtoken") {
            $this->resetWithToken();
        } else {
            $this->defaultLogin();
        }
    }

    protected function resetPassword(): void
    {
        $this->setSwapTag("html_title", "Reset password");
        $this->output->addSwapTagString(
            "page_content",
            file_get_contents("" . ROOTFOLDER . "/App/Endpoint/View/Login/reset.layout")
        );
    }

    protected function defaultLogin(): void
    {
        $this->setSwapTag("html_title", "Login");
        $this->output->addSwapTagString("why_logged_out", $this->session->getWhyLoggedOut());
        $this->output->addSwapTagString(
            "page_content",
            file_get_contents("" . ROOTFOLDER . "/App/Endpoint/View/Login/login.layout")
        );
    }

    protected function resetWithToken(): void
    {
        $this->setSwapTag("html_title", "Recover password");
        $this->output->addSwapTagString(
            "page_content",
            file_get_contents("" . ROOTFOLDER . "/App/Endpoint/View/Login/passwordrecover.layout")
        );
    }
}
