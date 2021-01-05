<?php

namespace App\Endpoints\Control\Avatar;

use App\Models\Avatar;
use App\Template\ViewAjax;
use avatar_helper;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $avatar = new Avatar();
        $input = new InputFilter();
        $avatarname = $input->postFilter("avatarname");
        $avataruuid = $input->postFilter("avataruuid", "uuid");
        if (count(explode(" ", $avatarname)) == 1) {
            $avatarname .= " Resident";
        }
        if (strlen($avatarname) < 5) {
            $this->output->setSwapTagString("message", "avatarname length must be 5 or longer");
            return;
        }
        if (strlen($avatarname) > 125) {
            $this->output->setSwapTagString("message", "avatarname length must be 125 or less");
            return;
        }
        if (strlen($avataruuid) != 36) {
            $this->output->setSwapTagString("message", "avataruuid must be a uuid");
            return;
        }
        if ($avatar->loadByField("avataruuid", $avataruuid) == true) {
            $this->output->setSwapTagString("message", "There is already an avatar with that uuid");
            return;
        }
        $avatar_helper = new avatar_helper();
        $status = $avatar_helper->load_or_create($avataruuid, $avatarname);
        if ($status == false) {
            $this->output->setSwapTagString("message", "Unable to create avatar");
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Avatar created");
        $this->output->setSwapTagString("redirect", "avatar");
    }
}
