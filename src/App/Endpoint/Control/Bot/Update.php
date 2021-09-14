<?php

namespace App\Endpoint\Control\Bot;

use App\R7\Model\Avatar;
use App\R7\Model\Botconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", null);
        if ($this->session->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can make changes to the bot config");
            return;
        }
        $input = new InputFilter();
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
        $notecards = $input->postBool("notecards");
        $ims = $input->postBool("ims");
        $invites = $input->postBool("invites");
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
