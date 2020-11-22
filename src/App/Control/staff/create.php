<?php

$status = false;
$ajax_reply->set_swap_tag_string("redirect", "staff");
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
        $staff->set_username($username);
        $staff->set_avatarlink($avatar->getId());
        $staff->set_email($email);
        $staff->set_phash(sha1("phash install" . microtime() . "" . $username));
        $staff->set_lhash(sha1("lhash install" . microtime() . "" . $username));
        $staff->set_psalt(sha1("psalt install" . microtime() . "" . $username));
        $staff->set_ownerlevel(false);
        $create_status = $staff->create_entry();
        if ($create_status["status"] == true) {
            $status = true;
            $ajax_reply->set_swap_tag_string("message", $lang["staff.cr.info.1"]);
        } else {
            $ajax_reply->set_swap_tag_string("message", sprintf($lang["staff.cr.error.10"], $create_status["message"]));
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $failed_on);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["staff.cr.error.9"]);
    $ajax_reply->set_swap_tag_string("redirect", "");
}
