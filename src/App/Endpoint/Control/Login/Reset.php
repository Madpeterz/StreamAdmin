<?php

namespace App\Endpoint\Control\Login;

use App\R7\Model\Avatar;
use App\R7\Model\Message;
use App\R7\Model\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Reset extends ViewAjax
{
    protected function sendMessageReset(Avatar $avatar, string $resetCode): bool
    {
        global $template_parts;
        $reset_url = $template_parts["url_base"] . "login/resetwithtoken/" . $resetCode;
        $message = new Message();
        $message->setAvatarLink($avatar->getId());
        $message->setMessage(sprintf("Web panel reset link: %1\$s expires in 1 hour", $reset_url));
        $add_status = $message->createEntry();
        if ($add_status["status"] == true) {
            return true;
        }
        return false;
    }
    public function process(): void
    {
        global $unixtime_hour;
        sleep(1);

        $input = new InputFilter();
        $avatar = new Avatar();
        $staff = new Staff();

        $slusername = $input->postFilter("slusername");
        $status = false;

        $username_bits = explode(" ", $slusername);
        if (count($username_bits) == 1) {
            $username_bits[] = "Resident";
        }
        $slusername = implode(" ", $username_bits);
        if ($avatar->loadByAvatarName($slusername) == true) {
            $status = $staff->loadByField("avatarLink", $avatar->getId());
        }
        if ($status == true) {
            if ($staff->getId() > 0) {
                $uid = $staff->createUID("emailResetCode", 8, 10);
                if ($uid["status"] == true) {
                    $staff->setEmailResetCode($uid["uid"]);
                    $staff->setEmailResetExpires((time() + $unixtime_hour));
                    $update_status = $staff->updateEntry();
                    if ($update_status["status"] == true) {
                        $status = $this->sendMessageReset($avatar, $uid["uid"]);
                    }
                }
            }
        }
        $this->setSwapTag("redirect", "here");
        $this->ok("If the account was found the reset code is on the way.");
    }
}
