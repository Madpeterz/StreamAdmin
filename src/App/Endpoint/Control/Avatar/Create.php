<?php

namespace App\Endpoint\Control\Avatar;

use App\Helpers\AvatarHelper;
use App\Models\Avatar;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $avatar = new Avatar();
        $input = new InputFilter();
        $avatarName = $input->postFilter("avatarName");
        $avatarUUID = $input->postFilter("avatarUUID", "uuid");
        if (count(explode(" ", $avatarName)) == 1) {
            $avatarName .= " Resident";
        }
        if (strlen($avatarName) < 5) {
            $this->setSwapTag("message", "avatarName length must be 5 or longer");
            return;
        }
        if (strlen($avatarName) > 125) {
            $this->setSwapTag("message", "avatarName length must be 125 or less");
            return;
        }
        if (strlen($avatarUUID) != 36) {
            $this->setSwapTag("message", "avatarUUID must be a uuid");
            return;
        }
        if ($avatar->loadByField("avatarUUID", $avatarUUID) == true) {
            $this->setSwapTag("message", "There is already an avatar with that uuid");
            return;
        }
        $avatar_helper = new AvatarHelper();
        $status = $avatar_helper->loadOrCreate($avatarUUID, $avatarName);
        if ($status == false) {
            $this->setSwapTag("message", "Unable to create avatar");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Avatar created");
        $this->setSwapTag("redirect", "avatar");
    }
}
