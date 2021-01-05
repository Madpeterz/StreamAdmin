<?php

namespace App\Endpoints\View\Login;

class DefaultView extends View
{
    public function process(): void
    {
        if ($this->area == "reset") {
            $this->resetPassword();
        } elseif ($this->area == "resetwithtoken") {
            $this->resetWithToken();
        } elseif ($this->area == "logout") {
            $this->logout();
        } else {
            $this->defaultLogin();
        }
    }

    protected function resetPassword(): void
    {
        $this->output->setSwapTagString("html_title", "Reset password");
        $this->output->addSwapTagString("page_content", file_get_contents("../App/View/Login/reset.layout"));
    }

    protected function defaultLogin(): void
    {
        $this->output->setSwapTagString("html_title", "Login");
        $this->output->addSwapTagString("why_logged_out", $this->session->getWhyLoggedOut());
        $this->output->addSwapTagString("page_content", file_get_contents("../App/View/Login/login.layout"));
    }

    protected function resetWithToken(): void
    {
        $this->output->setSwapTagString("html_title", "Recover password");
        $this->output->addSwapTagString("page_content", file_get_contents("../App/View/Login/passwordrecover.layout"));
    }

    protected function logout(): void
    {
        $this->session->endSession();
        $this->output->redirect("");
    }
}