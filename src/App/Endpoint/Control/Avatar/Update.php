<?php

namespace App\Endpoints\Control\Avatar;

use App\Models\Avatar;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new inputFilter();
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
        $this->setSwapTag("redirect", "avatar");
        $avatar = new Avatar();
        if ($avatar->loadByField("avatar_uid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find the avatar");
            return;
        }
        $whereConfig = [
            "fields" => ["avataruuid"],
            "values" => [$avataruuid],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($avatar->getTable(), $whereConfig);
        $expected_count = 0;
        if ($avatar->getAvataruuid() == $avataruuid) {
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
        $avatar->setAvatarname($avatarname);
        $avatar->setAvataruuid($avataruuid);
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
