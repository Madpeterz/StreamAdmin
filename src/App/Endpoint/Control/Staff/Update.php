<?php

namespace App\Endpoint\Control\Staff;

use App\Models\Staff;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {
        $staff = new Staff();
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Owner level access required");
            $this->setSwapTag("redirect", "staff/manage/" . $this->siteConfig->getPage() . "");
        }
        $username = $this->input->post("username")->checkStringLength(5, 40)->asString();
        if ($username == null) {
            $this->failed("Username failed:" . $this->input->getWhyFailed());
            return;
        }
        if ($staff->loadByUsername($username)->status == true) {
            $this->failed("There is already a staff member with that username");
            return;
        }
        $staff = new Staff();
        if ($staff->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to load staff member");
            return;
        }
        $staff->setUsername($username);
        $staff->setPhash(sha1("phash install" . microtime() . "" . $username));
        $staff->setLhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->setPsalt(sha1("psalt install" . microtime() . "" . $username));
        $update_status = $staff->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf("Unable to update staff member: %1\$s", $update_status->message)
            );
            return;
        }
        $this->ok("Staff member updated passwords reset");
    }
}
