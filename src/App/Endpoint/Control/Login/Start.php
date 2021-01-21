<?php

namespace App\Endpoint\Control\Login;

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
            $this->setSwapTag("message", "Username or Password is invaild");
            return;
        }
        if ($this->session->loginWithUsernamePassword($staffusername, $staffpassword) == false) {
            $this->setSwapTag("message", "Username or Password is invaild");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "logged in ^+^");
        $this->setSwapTag("redirect", "here");
    }
}
