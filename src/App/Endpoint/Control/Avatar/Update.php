<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\Avatar;
use App\Models\Sets\AvatarSet;
use App\Template\ControlAjax;

class Update extends ControlAjax
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
        $credits = 0;
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $credits = $this->input->post("credits")->checkInRange(0, 999999)->asInt();
            if ($credits == null) {
                $this->failed("Avatar credits failed:" . $this->input->getWhyFailed());
                return;
            }
        }
        $this->setSwapTag("redirect", "avatar");
        $avatar = new Avatar();
        if ($avatar->loadByAvatarUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find the avatar");
            return;
        }
        $whereConfig = [
            "fields" => ["avatarUUID"],
            "values" => [$avatarUUID],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $avatarSet = new AvatarSet();
        $count_check = $avatarSet->countInDB($whereConfig);
        $expected_count = 0;
        if ($avatar->getAvatarUUID() == $avatarUUID) {
            $expected_count = 1;
        }
        if ($count_check->status == false) {
            $this->failed("Unable to check if UUID in use");
            return;
        }
        if ($count_check->items != $expected_count) {
            $this->failed("Selected UUID is already in use");
            return;
        }
        $oldvalues = $avatar->objectToValueArray();
        $avatar->setAvatarName($avatarName);
        $avatar->setAvatarUUID($avatarUUID);
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $avatar->setCredits($credits);
        }

        $update_status = $avatar->updateEntry();
        if ($update_status->status == false) {
            $this->failed(sprintf("Unable to update avatar: %1\$s", $update_status->message));
            return;
        }
        $this->redirectWithMessage("Avatar updated");
        $this->createMultiAudit(
            $avatar->getId(),
            $avatar->getFields(),
            $oldvalues,
            $avatar->objectToValueArray()
        );
    }
}
