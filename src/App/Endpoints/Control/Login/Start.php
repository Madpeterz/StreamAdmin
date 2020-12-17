<?php

namespace App\Endpoints\Control\Login;

use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Start extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $staffusername = $input->postFilter("staffusername");
        $staffpassword = $input->postFilter("staffpassword");
        if ((strlen($staffusername) == 0) || (strlen($staffpassword) == 0)) {
            $this->output->setSwapTagString("message", "Username or Password is invaild");
            return;
        }
        if ($this->session->loginWithUsernamePassword($staffusername, $staffpassword) == false) {
            $this->output->setSwapTagString("message", "Username or Password is invaild");
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "logged in ^+^");
        $this->output->setSwapTagString("redirect", "here");
    }
}
