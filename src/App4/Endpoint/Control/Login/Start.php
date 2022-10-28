<?php

namespace App\Endpoint\Control\Login;

use App\Template\ControlAjax;

class Start extends ControlAjax
{
    public function process(): void
    {

        $staffusername = $this->input->post("staffusername")->checkStringLengthMin(3)->asString();
        $staffpassword = $this->input->post("staffpassword")->checkStringLengthMin(3)->asString();
        $this->failed("Username or Password is invaild");
        if (($staffusername == null) || ($staffpassword == null)) {
            return;
        }
        if ($this->siteConfig->getSession()->loginWithUsernamePassword($staffusername, $staffpassword) == false) {
            return;
        }
        $this->ok("logged in ^+^");
        $this->setSwapTag("redirect", "here");
    }
}
