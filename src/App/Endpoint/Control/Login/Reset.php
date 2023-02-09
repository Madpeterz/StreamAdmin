<?php

namespace App\Endpoint\Control\Login;

use App\Models\Avatar;
use App\Models\Message;
use App\Models\Staff;
use App\Framework\ViewAjax;
use App\Template\ControlAjax;
use YAPF\Framework\Responses\DbObjects\SingleLoadReply;

class Reset extends ControlAjax
{
    protected function sendMessageReset(Avatar $avatar, string $resetCode): bool
    {
        $reset_url = $this->siteConfig->getSiteURL() . "login/resetwithtoken/" . $resetCode;
        $message = new Message();
        $message->setAvatarLink($avatar->getId());
        $message->setMessage(sprintf("Web panel reset link: %1\$s expires in 1 hour", $reset_url));
        $add_status = $message->createEntry();
        return $add_status->status;
    }
    public function process(): void
    {
        sleep(1);
        $this->ok("Unable to find staff/avatar with given details");

        $avatar = new Avatar();
        $staff = new Staff();

        $slusername = $this->input->post("slusername")->asString();
        $status = new SingleLoadReply("Not set", false);

        $username_bits = explode(" ", $slusername);
        if (count($username_bits) == 1) {
            $username_bits[] = "Resident";
        }
        $slusername = implode(" ", $username_bits);
        if ($avatar->loadByAvatarName($slusername)->status == true) {
            $status = $staff->loadByAvatarLink($avatar->getId());
        }
        if ($status->status == false) {
            return;
        }
        if ($staff->isLoaded() == false) {
            return;
        }

        $uid = $staff->createUID("emailResetCode", 8);
        if ($uid->status == false) {
            return;
        }
        $staff->setEmailResetCode($uid->uid);
        $staff->setEmailResetExpires((time() + $this->siteConfig->unixtimeHour()));
        $update_status = $staff->updateEntry();
        if ($update_status->status == true) {
            $status = $this->sendMessageReset($avatar, $uid->uid);
        }
        if ($status == false) {
            return;
        }
        $this->redirectWithMessage("If the account was found the reset code is on the way.", "here");
        $this->createAuditLog(
            $staff->getId(),
            "reset code requested",
            "av:" . $avatar->getAvatarName(),
            "User:" . $staff->getUsername()
        );
    }
}
