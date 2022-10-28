<?php

namespace App\Endpoint\Control\Bot;

use App\Models\Avatar;
use App\Models\Botconfig;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", null);
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can make changes to the bot config");
            return;
        }

        $avataruid = $this->input->post("avataruid")->checkStringLength(8, 8)->asString();
        if ($avataruid == null) {
            $this->failed("Avatar UID failed:" . $this->input->getWhyFailed());
            return;
        }
        $secret = $this->input->post("secret")->checkStringLength(8, 30)->asString();
        if ($avataruid == null) {
            $this->failed("Secret failed:" . $this->input->getWhyFailed());
            return;
        }
        $httpMode = $this->input->post("httpMode")->asBool();
        if ($httpMode === null) {
            $httpMode = false;
        }
        $httpURL = $this->input->post("httpURL")->isUrl()->asString();
        $httpToken = $this->input->post("httpToken")->isNot("")->asArray();

        $notecards = $this->input->post("notecards")->asBool();
        if ($notecards === null) {
            $notecards = false;
        }
        $ims = $this->input->post("ims")->asBool();
        if ($ims === null) {
            $ims = false;
        }
        $invites = $this->input->post("invites")->asBool();
        if ($invites === null) {
            $invites = false;
        }
        $invite_uuid = $this->input->post("inviteGroupUUID")->isUuid()->asString();
        if ($invite_uuid == null) {
            if ($invites == true) {
                $this->failed("Group UUID is not vaild, please disable send invites or enter vaild UUID");
                return;
            }
            $invites = false;
        }


        $botconfig = new Botconfig();
        if ($botconfig->loadID(1)->status == false) {
            $this->failed("Unable to find bot config");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByAvatarUid($avataruid)->status == false) {
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
        if ($save_changes->status == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to save changes because: %1\$s", $save_changes->message)
            );
            return;
        }
        $this->setSwapTag("redirect", "config");
        $this->ok("Changes saved");
    }
}
