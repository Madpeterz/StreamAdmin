<?php

namespace App\Endpoint\Control\Staff;

use App\R7\Model\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $staff = new Staff();
        $input = new InputFilter();

        if ($this->session->getOwnerLevel() == false) {
            $this->setSwapTag("message", "Owner level access required");
            $this->setSwapTag("redirect", "staff/manage/" . $this->page . "");
        }

        $username = $input->postFilter("username");
        if (strlen($username) < 5) {
            $this->setSwapTag("message", "username length must be 5 or longer");
            return;
        }
        if (strlen($username) > 40) {
            $this->setSwapTag("message", "username length must be 40 or less");
            return;
        }
        if ($staff->loadByField("username", $username) == true) {
            $this->setSwapTag("message", "There is already a staff member with that username");
            return;
        }

        $staff = new Staff();
        if ($staff->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to load staff member");
            return;
        }
        $staff->setUsername($username);
        $staff->setPhash(sha1("phash install" . microtime() . "" . $username));
        $staff->setLhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->setPsalt(sha1("psalt install" . microtime() . "" . $username));
        $update_status = $staff->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update staff member: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "staff member updated passwords reset");
    }
}
