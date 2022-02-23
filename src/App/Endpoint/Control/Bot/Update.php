<?php

namespace App\Endpoint\Control\Bot;

use App\Models\Avatar;
use App\Models\Botconfig;
use App\Framework\ViewAjax;

class Update extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", null);
        if ($this->session->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can make changes to the bot config");
            return;
        }

        $avataruid = $input->postString("avataruid", 8, 8);
        if ($avataruid == null) {
            $this->failed("Avatar UID failed:" . $input->getWhyFailed());
            return;
        }
        $secret = $input->postString("secret", 30, 8);
        if ($avataruid == null) {
            $this->failed("Secret failed:" . $input->getWhyFailed());
            return;
        }
        $httpMode = $input->postBool("httpMode");
        if ($httpMode === null) {
            $httpMode = false;
        }
        $httpURL = $input->postUrl("httpURL");
        $httpToken = $input->postString("httpToken");

        $notecards = $input->postBool("notecards");
        if ($notecards === null) {
            $notecards = false;
        }
        $ims = $input->postBool("ims");
        if ($ims === null) {
            $ims = false;
        }
        $invites = $input->postBool("invites");
        if ($invites === null) {
            $invites = false;
        }
        $invite_uuid = $input->postUUID("inviteGroupUUID");
        if ($invite_uuid == null) {
            if ($invites == true) {
                $this->failed("Group UUID is not vaild, please disable send invites or enter vaild UUID");
                return;
            }
            $invites = false;
        }


        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) == false) {
            $this->failed("Unable to find bot config");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByField("avatarUid", $avataruid) == false) {
            $this->failed("Unable to load avatar to attach bot to");
            return;
        }
        if (($httpMode == true) && ($httpURL == null)) {
            $httpMode = false;
        }

        $botconfig->setHttpMode($httpMode);
        $botconfig->setHttpURL($httpURL);
        $botconfig->setHttpToken($httpToken);
        $botconfig->setAvatarLink($avatar->getId());
        $botconfig->setSecret($secret);
        $botconfig->setNotecards($notecards);
        $botconfig->setIms($ims);
        $botconfig->setInvites($invites);
        $botconfig->setInviteGroupUUID($invite_uuid);

        $save_changes = $botconfig->updateEntry();
        if ($save_changes["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to save changes because: %1\$s", $save_changes["message"])
            );
            return;
        }
        $this->setSwapTag("redirect", "config");
        $this->ok("Changes saved");
    }
}
