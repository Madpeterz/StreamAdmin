<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\Avatar;
use App\Framework\ViewAjax;

class Update extends ViewAjax
{
    public function process(): void
    {

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
        $this->setSwapTag("redirect", "avatar");
        $avatar = new Avatar();
        if ($avatar->loadByAvatarUid($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find the avatar");
            return;
        }
        $whereConfig = [
            "fields" => ["avatarUUID"],
            "values" => [$avatarUUID],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $count_check = $this->siteConfig->getSQL()->basicCountV2($avatar->getTable(), $whereConfig);
        $expected_count = 0;
        if ($avatar->getAvatarUUID() == $avatarUUID) {
            $expected_count = 1;
        }
        if ($count_check["status"] == false) {
            $this->failed("Unable to check if UUID in use");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->failed("Selected UUID is already in use");
            return;
        }
        $avatar->setAvatarName($avatarName);
        $avatar->setAvatarUUID($avatarUUID);
        $update_status = $avatar->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(sprintf("Unable to update avatar: %1\$s", $update_status["message"]));
            return;
        }
        $this->ok("Avatar updated");
    }
}
