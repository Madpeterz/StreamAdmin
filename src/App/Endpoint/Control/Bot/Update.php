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
        $this->setSwapTag("redirect", "config");
        if ($this->session->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can make changes to the bot config");
            return;
        }
        $input = new InputFilter();
        $avataruid = $input->postString("avataruid", 8, 8);
        if ($avataruid == null) {
            $this->failed("Avatar UID failed:" . $input->getWhyFailed());
        }
        $secret = $input->postString("secret", 30, 8);
        if ($avataruid == null) {
            $this->failed("Secret failed:" . $input->getWhyFailed());
        }
        $notecards = $input->postBool("notecards");
        $ims = $input->postBool("ims");

        $this->setSwapTag("redirect", null);
        if (strlen($avataruid) != 8) {
            $this->failed("avataruid length must be 8");
            return;
        }
        if (strlen($secret) < 8) {
            $this->failed("secret length can not be less than 8");
            return;
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
        $save_changes = $botconfig->updateEntry();
        if ($save_changes["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to save changes because: %1\$s", $save_changes["message"])
            );
            return;
        }
        $this->setSwapTag("redirect", null);
        $this->ok("Changes saved");
    }
}
