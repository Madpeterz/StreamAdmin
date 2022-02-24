<?php

namespace App\Endpoint\Control\Staff;

use App\Models\Staff;
use App\Framework\ViewAjax;

class Update extends ViewAjax
{
    public function process(): void
    {
        $staff = new Staff();


        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Owner level access required");
            $this->setSwapTag("redirect", "staff/manage/" . $this->siteConfig->getPage() . "");
        }

        $username = $this->input->post("username", 40, 5);
        if ($username == null) {
            $this->failed("Username failed:" . $this->input->getWhyFailed());
        }

        if ($staff->loadByUsername($username) == true) {
            $this->failed("There is already a staff member with that username");
            return;
        }

        $staff = new Staff();
        if ($staff->loadID($this->siteConfig->getPage()) == false) {
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
