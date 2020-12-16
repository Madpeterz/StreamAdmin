<?php

namespace App\Control\Bot;

use App\Models\Avatar;
use App\Models\Botconfig;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $this->output->setSwapTagString("redirect", "config");
        if ($this->session->getOwnerLevel() == false) {
            $this->output->setSwapTagString("message", "Sorry only owners can make changes to the bot config");
            return;
        }
        $input = new InputFilter();
        $avataruid = $input->postFilter("avataruid");
        $secret = $input->postFilter("secret");
        $notecards = $input->postFilter("notecards", "bool");
        $ims = $input->postFilter("ims", "bool");
        $this->output->setSwapTagString("redirect", null);
        if (strlen($avataruid) != 8) {
            $this->output->setSwapTagString("message", "avataruid length must be 8");
            return;
        }
        if (strlen($secret) < 8) {
            $this->output->setSwapTagString("message", "secret length can not be less than 8");
            return;
        }
        $botconfig = new Botconfig();
        if ($botconfig->loadID(1) == false) {
            $this->output->setSwapTagString("message", "Unable to find bot config");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadByField("avatar_uid", $avataruid) == false) {
            $this->output->setSwapTagString("message", "Unable to load avatar to attach bot to");
            return;
        }
        $botconfig->setAvatarlink($avatar->getId());
        $botconfig->setSecret($secret);
        $botconfig->setNotecards($notecards);
        $botconfig->setIms($ims);
        $save_changes = $botconfig->updateEntry();
        if ($save_changes["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to save changes because: %1\$s", $save_changes["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("redirect", null);
        $this->output->setSwapTagString("message", "Changes saved");
    }
}
