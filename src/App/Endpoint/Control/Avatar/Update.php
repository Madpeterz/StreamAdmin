<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\Avatar;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new inputFilter();
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
        $this->setSwapTag("redirect", "avatar");
        $avatar = new Avatar();
        if ($avatar->loadByField("avatarUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find the avatar");
            return;
        }
        $whereConfig = [
            "fields" => ["avatarUUID"],
            "values" => [$avatarUUID],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($avatar->getTable(), $whereConfig);
        $expected_count = 0;
        if ($avatar->getAvatarUUID() == $avatarUUID) {
            $expected_count = 1;
        }
        if ($count_check["status"] == false) {
            $this->setSwapTag("message", "Unable to check if UUID in use");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->setSwapTag("message", "Selected UUID is already in use");
            return;
        }
        $avatar->setAvatarName($avatarName);
        $avatar->setAvatarUUID($avatarUUID);
        $update_status = $avatar->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update avatar: %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Avatar updated");
    }
}
