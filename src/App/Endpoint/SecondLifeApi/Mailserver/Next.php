<?php

namespace App\Endpoint\SecondLifeApi\Mailserver;

use App\Models\Avatar;
use App\Models\Sets\MessageSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("hasmessage", 0);
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "SystemAPI access only - please contact support");
            return;
        }
        $message_set = new MessageSet();
        $message_set->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
        if ($message_set->getCount() == 0) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "nowork");
            return;
        }
        $message = $message_set->getFirst();
        $avatar = new Avatar();
        if ($avatar->loadID($message->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to find avatar attached to message");
            return;
        }

        $remove_status = $message->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag("message", "Unable to remove message from the mailbox");
            return;
        }
        $this->setSwapTag("hasmessage", true);
        $this->setSwapTag("message", $message->getMessage());
        $this->setSwapTag("avatarUUID", $avatar->getAvatarUUID());
        $this->setSwapTag("status", true);
    }
}
