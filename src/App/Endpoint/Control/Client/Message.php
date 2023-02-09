<?php

namespace App\Endpoint\Control\Client;

use App\Endpoint\Control\Outbox\Send;
use App\Models\Rental;
use App\Template\ControlAjax;

class Message extends ControlAjax
{
    public function process(): void
    {
        $rental = new Rental();
        if ($rental->loadByRentalUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find client");
            $this->setSwapTag("redirect", "client");
            return;
        }
        $message = $this->input->post("mail")->checkStringLength(10, 800)->asString();
        if ($message == null) {
            $this->failed("message failed:" . $this->input->getWhyFailed());
            return;
        }

        $Send = new Send();
        global $_POST;
        $_POST["message"] = $message;
        $_POST["max_avatars"] = 1;
        $_POST["source"] = "Selectedrental";
        $_POST["source_id"] = $rental->getId();
        $_POST["avatarids"] = [$rental->getAvatarLink()];
        $Send->process();
        $reply = $Send->getOutputObject();
        if ($reply->getSwapTagBool("status") == false) {
            $this->failed($reply->getSwapTagString("message"));
            return;
        }

        $this->redirectWithMessage("Message added to outbox");
        return;
    }
}
