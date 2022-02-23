<?php

namespace App\Endpoint\Control\Login;

use App\Framework\SessionControl;
use App\Models\Avatar;
use App\Models\Staff;
use App\Framework\ViewAjax;

class Resetnow extends ViewAjax
{
    public function process(): void
    {
        sleep(1);

        $slusername = $input->postString("slusername");
        $token = $input->postString("token");
        $newpw1 = $input->postString("newpassword1", 30, 7);
        if ($newpw1 == null) {
            $this->failed("New password failed:" . $input->getWhyFailed());
            return;
        }
        $newpw2 = $input->postString("newpassword2", 30, 7);
        if ($newpw2 == null) {
            $this->failed("New password (Repeated) failed:" . $input->getWhyFailed());
            return;
        }
        if ($newpw1 != $newpw2) {
            $this->failed(
                "New passwords do not match"
            );
            return;
        }

        $this->failed("Something went wrong with your request");
        $username_bits = explode(" ", $slusername);
        if (count($username_bits) == 1) {
            $username_bits[] = "Resident";
        }
        $slusername = implode(" ", $username_bits);
        $avatar = new Avatar();
        if ($avatar->loadByAvatarName($slusername) == false) {
            return;
        }
        $staff = new Staff();
        if ($staff->loadByField("avatarLink", $avatar->getId()) == false) {
            return;
        }
        if ($staff->getEmailResetCode() != $token) {
            return;
        }
        if ($staff->getEmailResetExpires() <= time()) {
            $this->failed(
                "Your token has expired, please request a new one"
            );
            return;
        }
        $this->applyNewPassword($staff, $newpw1);
    }
    protected function applyNewPassword(Staff $staff, string $newpw1): void
    {
        $session_helper = new SessionControl();
        $session_helper->attachStaffMember($staff);
        $update_status = $session_helper->updatePassword($newpw1);
        if ($update_status["status"] == false) {
            $this->failed("Something went wrong updating your password");
            return;
        }
        $staff->setEmailResetCode(null);
        $staff->setEmailResetExpires(time() - 1);
        $update_status = $staff->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed("Unable to finalize changes to your account");
            return;
        }
        $this->ok("Password updated please login");
        $this->setSwapTag("redirect", "login");
    }
}
