<?php

namespace App\Endpoints\Control\Staff;

use App\Models\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $staff = new Staff();
        $input = new InputFilter();

        if ($this->session->getOwnerLevel() == false) {
            $this->output->setSwapTagString("message", "Owner level access required");
            $this->output->setSwapTagString("redirect", "staff/manage/" . $this->page . "");
        }

        $username = $input->postFilter("username");
        $email = $input->postFilter("email");
        $bits = explode("@", $email);
        if (strlen($username) < 5) {
            $this->output->setSwapTagString("message", "username length must be 5 or longer");
            return;
        }
        if (strlen($username) > 40) {
            $this->output->setSwapTagString("message", "username length must be 40 or less");
            return;
        }
        if (count($bits) != 2) {
            $this->output->setSwapTagString("message", "Email address is not formated correctly");
            return;
        }
        if ($staff->loadByField("username", $username) == true) {
            $this->output->setSwapTagString("message", "There is already a staff member with that username");
            return;
        }
        if (strlen($email) > 100) {
            $this->output->setSwapTagString("message", "email length must be 100 or less");
            return;
        }

        $staff = new Staff();
        if ($staff->loadID($this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to load staff member");
            return;
        }
        $staff->setUsername($username);
        $staff->setEmail($email);
        $staff->setPhash(sha1("phash install" . microtime() . "" . $username));
        $staff->setLhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->setPsalt(sha1("psalt install" . microtime() . "" . $username));
        $update_status = $staff->updateEntry();
        if ($update_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to update staff member: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "staff member updated passwords reset");
    }
}
