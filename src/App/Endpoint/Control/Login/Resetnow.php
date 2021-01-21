<?php

namespace App\Endpoint\Control\Login;

use App\Framework\SessionControl;
use App\Models\Avatar;
use App\Models\Staff;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Resetnow extends ViewAjax
{
    public function process(): void
    {
        sleep(1);
        $input = new InputFilter();
        $slusername = $input->postFilter("slusername");
        $token = $input->postFilter("token");
        $newpw1 = $input->postFilter("newpassword1");
        $newpw2 = $input->postFilter("newpassword2");

        $status = false;
        if ($newpw1 != $newpw2) {
            $this->setSwapTag(
                "message",
                "New passwords do not match"
            );
            return;
        }
        if (strlen($newpw1) < 7) {
            $this->setSwapTag(
                "message",
                "New password is to short min length 7"
            );
            return;
        }

        $this->setSwapTag("message", "Something went wrong with your request");
        $username_bits = explode(" ", $slusername);
        if (count($username_bits) == 1) {
            $username_bits[] = "Resident";
        }
        $slusername = implode(" ", $username_bits);
        $avatar = new Avatar();
        $status = false;
        if ($avatar->loadByField("avatarName", $slusername) == true) {
            $staff = new Staff();
            if ($staff->loadByField("avatarLink", $avatar->getId()) == true) {
                if ($staff->getEmailResetCode() == $token) {
                    if ($staff->getEmailResetExpires() <= time()) {
                        $this->setSwapTag(
                            "message",
                            "Your token has expired, please request a new one"
                        );
                        return;
                    }
                    $this->applyNewPassword($staff, $newpw1);
                }
            }
        }
    }
    protected function applyNewPassword(Staff $staff, string $newpw1): string
    {
        $session_helper = new SessionControl();
        $session_helper->attachStaffMember($staff);
        $update_status = $session_helper->updatePassword($newpw1);
        if ($update_status["status"] == false) {
            return "Something went wrong updating your password";
        }
        $staff->setEmailResetCode(null);
        $staff->setEmailResetExpires(time() - 1);
        $update_status = $staff->updateEntry();
        if ($update_status["status"] == false) {
            return "Unable to finalize changes to your account";
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Password updated please login");
        $this->setSwapTag("redirect", "login");
        return "";
    }
}
