<?php

namespace App\Endpoint\Control\Login;

use App\Framework\ViewAjax;

class Start extends ViewAjax
{
    public function process(): void
    {

        $staffusername = $this->post("staffusername")->checkStringLengthMin(3)->asString();
        $staffpassword = $this->post("staffpassword")->checkStringLengthMin(3)->asString();
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
