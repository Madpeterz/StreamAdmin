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
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can make changes to the bot config");
            return;
        }

        $avataruid = $this->post("avataruid")->checkStringLength(8, 8)->asString();
        if ($avataruid == null) {
            $this->failed("Avatar UID failed:" . $this->input->getWhyFailed());
            return;
        }
        $secret = $this->post("secret")->checkStringLength(8, 30)->asString();
        if ($avataruid == null) {
            $this->failed("Secret failed:" . $this->input->getWhyFailed());
            return;
        }
        $httpMode = $this->post("httpMode")->asBool();
        if ($httpMode === null) {
            $httpMode = false;
        }
        $httpURL = $this->post("httpURL")->isUrl()->asString();
        $httpToken = $this->post("httpToken")->isNot("")->asArray();

        $notecards = $this->post("notecards")->asBool();
        if ($notecards === null) {
            $notecards = false;
        }
        $ims = $this->post("ims")->asBool();
        if ($ims === null) {
            $ims = false;
        }
        $invites = $this->post("invites")->asBool();
        if ($invites === null) {
            $invites = false;
        }
        $invite_uuid = $this->post("inviteGroupUUID");
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
