<?php

namespace App\Endpoint\Control\Avatar;

use App\Helpers\AvatarHelper;
use App\Models\Avatar;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $avatar = new Avatar();
        $avatarName = $this->input->post("avatarName")->checkStringLength(5, 125)->asString();
        if ($avatarName == null) {
            $this->failed("Avatar name failed:" . $this->input->getWhyFailed());
            return;
        }
        $avatarUUID = $this->input->post("avatarUUID")->isUuid()->asString();
        if ($avatarUUID == null) {
            $this->failed("Avatar UUID failed:" . $this->input->getWhyFailed());
            return;
        }
        if ($avatar->loadByAvatarUUID($avatarUUID)->status == true) {
            $this->failed("There is already an avatar with that uuid");
            return;
        }
        $avatar_helper = new AvatarHelper();
        $status = $avatar_helper->loadOrCreate($avatarUUID, $avatarName);
        if ($status == false) {
            $this->failed("Unable to create avatar");
            return;
        }
        $this->redirectWithMessage("Avatar created");
    }
}
