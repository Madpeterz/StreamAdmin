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
            $this->setSwapTag("message", "avatarname length must be 5 or longer");
            return;
        }
        if (strlen($avatarname) > 125) {
            $this->setSwapTag("message", "avatarname length must be 125 or less");
            return;
        }
        if (strlen($avataruuid) != 36) {
            $this->setSwapTag("message", "avataruuid must be a uuid");
            return;
        }
        if ($avatar->loadByField("avataruuid", $avataruuid) == true) {
            $this->setSwapTag("message", "There is already an avatar with that uuid");
            return;
        }
        $avatar_helper = new avatar_helper();
        $status = $avatar_helper->loadOrCreate($avataruuid, $avatarname);
        if ($status == false) {
            $this->setSwapTag("message", "Unable to create avatar");
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Avatar created");
        $this->setSwapTag("redirect", "avatar");
    }
}
