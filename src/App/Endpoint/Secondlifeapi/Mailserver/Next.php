<?php

namespace App\Endpoint\Secondlifeapi\Mailserver;

use App\Models\Avatar;
use App\Models\Sets\MessageSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("hasmessage", false);
        if ($this->owner_override == false) {
            $this->failed("SystemAPI access only - please contact support");
            return;
        }
        $message_set = new MessageSet();
        $message_set->loadNewest(limit:1, orderDirection:"ASC");
        if ($message_set->getCount() == 0) {
            $this->ok("nowork");
            return;
        }
        $message = $message_set->getFirst();
        $avatar = new Avatar();
        $avatar->loadID($message->getAvatarLink());
        if ($avatar->isLoaded() == false) {
            $this->failed("Unable to find avatar attached to message");
            return;
        }

        if ($message->removeEntry()->status == false) {
            $this->failed("Unable to remove message from the mailbox");
            return;
        }
        $this->setSwapTag("hasmessage", true);
        $this->setSwapTag("avatarUUID", $avatar->getAvatarUUID());
        $this->ok($message->getMessage());
    }
}
