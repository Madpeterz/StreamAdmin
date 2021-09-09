<?php

namespace App\Endpoint\Control\Avatar;

use App\Helpers\AvatarHelper;
use App\R7\Model\Avatar;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $avatar = new Avatar();
        $input = new InputFilter();
        $avatarName = $input->postString("avatarName", 125, 5);
        $avatarUUID = $input->postUUID("avatarUUID");
        if ($avatarName == null) {
            $this->failed("Avatar name failed:" . $input->getWhyFailed());
        }
        if ($avatarUUID == null) {
            $this->failed("Avatar UUID failed:" . $input->getWhyFailed());
            return;
        }

        if ($avatar->loadByAvatarUUID($avatarUUID) == true) {
            $this->failed("There is already an avatar with that uuid");
            return;
        }
        $avatar_helper = new AvatarHelper();
        $status = $avatar_helper->loadOrCreate($avatarUUID, $avatarName);
        if ($status == false) {
            $this->failed("Unable to create avatar");
            return;
        }
        $this->ok("Avatar created");
        $this->setSwapTag("redirect", "avatar");
    }
}
