<?php

namespace App\Endpoints\SecondLifeApi\Mailserver;

use App\Models\Avatar;
use App\Models\MessageSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $this->output->setSwapTagString("hasmessage", 0);
        if ($this->owner_override == false) {
            $this->output->setSwapTagString("message", "SystemAPI access only - please contact support");
            return;
        }
        $message_set = new MessageSet();
        $message_set->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($message_set->getCount() == 0) {
            $this->output->setSwapTagString("message", "nowork");
            return;
        }
        $message = $message_set->getFirst();
        $avatar = new Avatar();
        if ($avatar->loadID($message->getAvatarlink()) == false) {
            $this->output->setSwapTagString("message", "Unable to find avatar attached to message");
            return;
        }

        $remove_status = $message->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to remove message from the mailbox");
            return;
        }
        $this->output->setSwapTagString("hasmessage", 1);
        $this->output->setSwapTagString("message", $message->getMessage());
        $this->output->setSwapTagString("avataruuid", $avatar->getAvataruuid());
        $this->output->setSwapTagString("status", "true");
    }
}
