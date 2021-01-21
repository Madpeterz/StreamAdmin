<?php

namespace App\Endpoint\Control\Staff;

use App\Models\Avatar;
use App\Models\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", "staff");
        if ($this->session->getOwnerLevel() == false) {
            $this->setSwapTag("message", "Owner level access required");
            $this->setSwapTag("redirect", "");
        }
        $staff = new Staff();
        $avatar = new Avatar();
        $input = new inputFilter();
        $avataruid = $input->postFilter("avataruid");
        $username = $input->postFilter("username");
        $email = $input->postFilter("email");
        $bits = explode("@", $email);

        if (strlen($username) < 5) {
            $this->setSwapTag("message", "username length must be 5 or longer");
            return;
        }
        if (strlen($username) > 40) {
            $this->setSwapTag("message", "username length must be 40 or less");
            return;
        }
        if (strlen($avataruid) != 8) {
            $this->setSwapTag("message", "avataruid length must be 8");
            return;
        }
        if ($staff->loadByField("username", $username) == true) {
            $this->setSwapTag("message", "There is already a staff member with that username");
            return;
        }
        if ($avatar->loadByField("avatarUid", $avataruid) == false) {
            $this->setSwapTag("message", "Unable to find avatar with matching uid");
            return;
        }
        if (count($bits) != 2) {
            $this->setSwapTag("message", "Email address is not formated correctly");
            return;
        }
        if ($staff->loadByField("email", $email) == true) {
            $this->setSwapTag("message", "There is already a staff member using that email");
            return;
        }
        if (strlen($email) > 100) {
            $this->setSwapTag("message", "email length must be 100 or less");
            return;
        }
        $staff = new Staff();
        $staff->setUsername($username);
        $staff->setAvatarLink($avatar->getId());
        $staff->setEmail($email);
        $staff->setPhash(sha1("phash install" . microtime() . "" . $username));
        $staff->setLhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->setPsalt(sha1("psalt install" . microtime() . "" . $username));
        $staff->setOwnerLevel(false);
        $create_status = $staff->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create staff member: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "staff member created");
    }
}
