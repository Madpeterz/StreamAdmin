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
            $this->failed("Owner level access required");
            $this->setSwapTag("redirect", "staff/manage/" . $this->page . "");
        }

        $username = $input->postString("username", 40, 5);
        if ($username == null) {
            $this->failed("Username failed:" . $input->getWhyFailed());
        }

        if ($staff->loadByUsername($username) == true) {
            $this->failed("There is already a staff member with that username");
            return;
        }

        $staff = new Staff();
        if ($staff->loadID($this->page) == false) {
            $this->failed("Unable to load staff member");
            return;
        }
        $staff->setUsername($username);
        $staff->setPhash(sha1("phash install" . microtime() . "" . $username));
        $staff->setLhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->setPsalt(sha1("psalt install" . microtime() . "" . $username));
        $update_status = $staff->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(
                sprintf("Unable to update staff member: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->ok("Staff member updated passwords reset");
    }
}
