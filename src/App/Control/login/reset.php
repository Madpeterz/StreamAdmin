<?php

namespace App\Control\Login;

use App\Models\Avatar;
use App\Models\Message;
use App\Models\Staff;
use App\Template\ViewAjax;
use email_helper;
use YAPF\InputFilter\InputFilter;

class Reset extends ViewAjax
{
    protected $reset_message = "
    You or someone pretending to be you<br/>
    has requested your password be reset and emailed to your<br/>
    email address so I said ok<br/>
    <br/>
    Your reset token is: %1\$s<br/>
    <br/>
    <a href=\"%2\$s\">Reset now</a><br/>
    This link expires after 1 hour.";

    protected function sendEmailReset(Staff $staff, string $resetCode): bool
    {
        global $template_parts;
        $reset_url = $template_parts["url_base"] . "login/resetwithtoken/" . $resetCode;
        $email_helper = new email_helper();
        $status_reply = $email_helper->send_email(
            $staff->getEmail(),
            "StreamAdmin password reset",
            sprintf($this->reset_message, $resetCode, $reset_url)
        );
        if ($status_reply["status"] == true) {
            return true;
        }
        return false;
    }
    protected function sendMessageReset(Staff $staff, Avatar $avatar, string $resetCode): bool
    {
        global $template_parts;
        $reset_url = $template_parts["url_base"] . "login/resetwithtoken/" . $resetCode;
        $message = new Message();
        $message->setAvatarlink($avatar->getId());
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
        $bits = explode("@", $slusername);
        $contact_via = "sl";
        $status = false;

        if (count($bits) == 2) {
            $contact_via = "email";
            $status = $staff->loadByField("email", $slusername);
        } else {
            $username_bits = explode(" ", $slusername);
            if (count($username_bits) == 1) {
                $username_bits[] = "Resident";
            }
            $slusername = implode(" ", $username_bits);
            if ($avatar->loadByField("avatarname", $slusername) == true) {
                $status = $staff->loadByField("avatarlink", $avatar->getId());
            }
        }
        if ($status == true) {
            if ($staff->getId() > 0) {
                $uid = $staff->createUID("email_reset_code", 8, 10);
                if ($uid["status"] == true) {
                    $staff->setEmail_reset_code($uid["uid"]);
                    $staff->setEmail_reset_expires((time() + $unixtime_hour));
                    $update_status = $staff->updateEntry();
                    if ($update_status["status"] == true) {
                        if ($contact_via == "email") {
                            $status = $this->sendEmailReset($staff, $uid["uid"]);
                        } else {
                            $status = $this->sendMessageReset($staff, $avatar, $uid["uid"]);
                        }
                    }
                }
            }
        }
        $this->output->setSwapTagString("status", (string)$status);
        $this->output->setSwapTagString("redirect", "here");
        $this->output->setSwapTagString("message", "If the account was found the reset code is on the way.");
    }
}
