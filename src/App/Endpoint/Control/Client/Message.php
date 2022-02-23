<?php

namespace App\Endpoint\Control\Client;

use App\Endpoint\Control\Outbox\Send;
use App\Models\Rental;
use App\Framework\ViewAjax;

class Message extends ViewAjax
{
    public function process(): void
    {
        $rental = new Rental();

        if ($rental->loadByRentalUid($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find client");
            $this->setSwapTag("redirect", "client");
            return;
        }
        $message = $input->postString("mail", 800, 10);
        if ($message == null) {
            $this->failed("message failed:" . $input->getWhyFailed());
            return;
        }

        $Send = new Send();
        global $_POST;
        $_POST["message"] = $message;
        $_POST["max_avatars"] = 1;
        $_POST["source"] = "selectedRental";
        $_POST["source_id"] = $rental->getId();
        $_POST["avatarids"] = [$rental->getAvatarLink()];
        $Send->process();
        $reply = $Send->getOutputObject();
        if ($reply->getSwapTagBool("status") == false) {
            $this->failed($reply->getSwapTagString("message"));
            return;
        }

        $this->ok("Message added to outbox");
        return;
    }
}
