<?php

$status = false;
$this->output->setSwapTagString("redirect", "staff");
if ($session->getOwnerLevel() == true) {
    $staff = new staff();
    $avatar = new avatar();

    $input = new inputFilter();
    $avataruid = $input->postFilter("avataruid");
    $username = $input->postFilter("username");
    $email = $input->postFilter("email");
    $bits = explode("@", $email);

        $failed_on = "";
    if (strlen($username) < 5) {
        $failed_on .= $lang["staff.cr.error.1"];
    } elseif (strlen($username) > 40) {
        $failed_on .= $lang["staff.cr.error.2"];
    } elseif (strlen($avataruid) != 8) {
        $failed_on .= $lang["staff.cr.error.3"];
    } elseif ($staff->loadByField("username", $username) == true) {
        $failed_on .= $lang["staff.cr.error.4"];
    } elseif ($avatar->loadByField("avatar_uid", $avataruid) == false) {
        $failed_on .= $lang["staff.cr.error.5"];
    } elseif (count($bits) != 2) {
        $failed_on .= $lang["staff.cr.error.8"];
    } elseif ($staff->loadByField("email", $email) == true) {
        $failed_on .= $lang["staff.cr.error.6"];
    } elseif (strlen($email) > 100) {
        $failed_on .= $lang["staff.cr.error.7"];
    }

    if ($failed_on == "") {
        $staff = new staff();
        $staff->setUsername($username);
        $staff->setAvatarlink($avatar->getId());
        $staff->setEmail($email);
        $staff->set_phash(sha1("phash install" . microtime() . "" . $username));
        $staff->set_lhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->set_psalt(sha1("psalt install" . microtime() . "" . $username));
        $staff->set_ownerlevel(false);
        $create_status = $staff->createEntry();
        if ($create_status["status"] == true) {
            $status = true;
            $this->output->setSwapTagString("message", $lang["staff.cr.info.1"]);
        } else {
            $this->output->setSwapTagString("message", sprintf($lang["staff.cr.error.10"], $create_status["message"]));
        }
    } else {
        $this->output->setSwapTagString("message", $failed_on);
    }
} else {
    $this->output->setSwapTagString("message", $lang["staff.cr.error.9"]);
    $this->output->setSwapTagString("redirect", "");
}
