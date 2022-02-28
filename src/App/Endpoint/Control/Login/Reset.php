<?php

namespace App\Endpoint\Control\Login;

use App\Models\Avatar;
use App\Models\Message;
use App\Models\Staff;
use App\Framework\ViewAjax;

class Reset extends ViewAjax
{
    protected function sendMessageReset(Avatar $avatar, string $resetCode): bool
    {
        global $template_parts;
        $reset_url = $this->siteConfig->getSiteURL() . "login/resetwithtoken/" . $resetCode;
        $message = new Message();
        $message->setAvatarLink($avatar->getId());
        $message->setMessage(sprintf("Web panel reset link: %1\$s expires in 1 hour", $reset_url));
        $add_status = $message->createEntry();
        return $add_status["status"];
    }
    public function process(): void
    {
        global $unixtime_hour;
        sleep(1);

        $avatar = new Avatar();
        $staff = new Staff();

        $slusername = $this->post("slusername")->asString();
        $status = false;

        $username_bits = explode(" ", $slusername);
        if (count($username_bits) == 1) {
            $username_bits[] = "Resident";
        }
        $slusername = implode(" ", $username_bits);
        if ($avatar->loadByAvatarName($slusername) == true) {
            $status = $staff->loadByField("avatarLink", $avatar->getId());
        }
        if ($status == false) {
            $this->failed("Unable to find staff/avatar with given details");
            return;
        }
        if ($staff->isLoaded() == false) {
            $this->failed("Unable to find staff/avatar with given details");
            return;
        }

        $uid = $staff->createUID("emailResetCode", 8);
        if ($uid["status"] == false) {
            $this->failed("Unable to find staff/avatar with given details");
            return;
        }
        $staff->setEmailResetCode($uid["uid"]);
        $staff->setEmailResetExpires((time() + $unixtime_hour));
        $update_status = $staff->updateEntry();
        if ($update_status["status"] == true) {
            $status = $this->sendMessageReset($avatar, $uid["uid"]);
        }
        if ($status == false) {
            $this->failed("Unable to find staff/avatar with given details");
            return;
        }
        $this->setSwapTag("redirect", "here");
        $this->ok("If the account was found the reset code is on the way.");
    }
}
