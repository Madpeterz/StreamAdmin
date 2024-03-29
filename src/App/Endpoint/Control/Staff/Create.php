<?php

namespace App\Endpoint\Control\Staff;

use App\R7\Model\Avatar;
use App\R7\Model\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", "staff");
        if ($this->session->getOwnerLevel() == false) {
            $this->failed("Owner level access required");
            $this->setSwapTag("redirect", "");
        }
        $staff = new Staff();
        $avatar = new Avatar();
        $input = new inputFilter();
        $avataruid = $input->postString("avataruid", 8, 8);
        if ($avataruid == null) {
            $this->failed("Avatar UID failed:" . $input->getWhyFailed());
        }
        $username = $input->postString("username", 40, 5);
        if ($username == null) {
            $this->failed("Username failed:" . $input->getWhyFailed());
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
