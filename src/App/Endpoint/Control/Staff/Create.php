<?php

namespace App\Endpoint\Control\Staff;

use App\Models\Avatar;
use App\Models\Staff;
use App\Framework\ViewAjax;

class Create extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", "staff");
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Owner level access required");
            $this->setSwapTag("redirect", "");
        }
        $staff = new Staff();
        $avatar = new Avatar();

        $avataruid = $this->input->post("avataruid", 8, 8);
        if ($avataruid == null) {
            $this->failed("Avatar UID failed:" . $this->input->getWhyFailed());
        }
        $username = $this->input->post("username", 40, 5);
        if ($username == null) {
            $this->failed("Username failed:" . $this->input->getWhyFailed());
        }


        if ($staff->loadByUsername($username) == true) {
            $this->failed("There is already a staff member with that username");
            return;
        }
        if ($avatar->loadByAvatarUid($avataruid) == false) {
            $this->failed("Unable to find avatar with matching uid");
            return;
        }
        $staff = new Staff();
        $staff->setUsername($username);
        $staff->setAvatarLink($avatar->getId());
        $staff->setPhash(sha1("phash install" . microtime() . "" . $username));
        $staff->setLhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->setPsalt(sha1("psalt install" . microtime() . "" . $username));
        $staff->setOwnerLevel(false);
        $create_status = $staff->createEntry();
        if ($create_status["status"] == false) {
            $this->failed(
                sprintf("Unable to create staff member: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->ok("Staff member created");
    }
}
