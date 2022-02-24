<?php

namespace App\Endpoint\Control\Login;

use App\Framework\ViewAjax;

class Start extends ViewAjax
{
    public function process(): void
    {

        $staffusername = $this->input->post("staffusername");
        $staffpassword = $this->input->post("staffpassword");
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
