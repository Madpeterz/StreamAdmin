<?php

namespace App\Endpoint\Control\Bot;

use App\Models\Avatar;
use App\Models\Botconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", "config");
        if ($this->session->getOwnerLevel() == false) {
            $this->setSwapTag("message", "Sorry only owners can make changes to the bot config");
            return;
        }
        $input = new InputFilter();
        $avataruid = $input->postFilter("avataruid");
        $secret = $input->postFilter("secret");
        $notecards = $input->postFilter("notecards", "bool");
        $ims = $input->postFilter("ims", "bool");
        $this->setSwapTag("redirect", null);
        if (strlen($avataruid) != 8) {
            $this->setSwapTag("message", "avataruid length must be 8");
            return;
        }
        if (strlen($secret) < 8) {
            $this->setSwapTag("message", "secret length can not be less than 8");
            return;
        }
        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) == false) {
            $this->setSwapTag("message", "Unable to find bot config");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByField("avatarUid", $avataruid) == false) {
            $this->setSwapTag("message", "Unable to load avatar to attach bot to");
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
        $this->setSwapTag("status", "true");
        $this->setSwapTag("redirect", null);
        $this->setSwapTag("message", "Changes saved");
    }
}
