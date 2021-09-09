<?php

namespace App\Endpoint\Control\Login;

use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Start extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $staffusername = $input->postString("staffusername");
        $staffpassword = $input->postString("staffpassword");
        $this->failed("Username or Password is invaild");
        if ((strlen($staffusername) == 0) || (strlen($staffpassword) == 0)) {
            return;
        }
        if ($this->session->loginWithUsernamePassword($staffusername, $staffpassword) == false) {
            return;
        }
        $this->ok("logged in ^+^");
        $this->setSwapTag("redirect", "here");
    }
}
