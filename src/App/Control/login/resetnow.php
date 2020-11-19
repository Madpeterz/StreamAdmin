<?php

sleep(1);
$input = new inputFilter();
$slusername = $input->postFilter("slusername");
$token = $input->postFilter("token");
$newpw1 = $input->postFilter("newpassword1");
$newpw2 = $input->postFilter("newpassword2");

$status = false;
$failed_on = "";
if ($newpw1 == $newpw2) {
    if (strlen($newpw1) >= 7) {
        $username_bits = explode(" ", $slusername);
        if (count($username_bits) == 1) {
            $username_bits[] = "Resident";
        }
        $slusername = implode(" ", $username_bits);
        $avatar = new avatar();
        $status = false;
        if ($avatar->loadByField("avatarname", $slusername) == true) {
            $staff = new staff();
            if ($staff->loadByField("avatarlink", $avatar->getId()) == true) {
                if ($staff->get_email_reset_code() == $token) {
                    if ($staff->get_email_reset_expires() > time()) {
                        $session_helper = new session_control();
                        $session_helper->attach_staff_member($staff);
                        $update_status = $session_helper->update_password($newpw1);
                        if ($update_status["status"] == true) {
                            $staff->set_email_reset_code(null);
                            $staff->set_email_reset_expires(time() - 1);
                            $update_status = $staff->save_changes();
                            if ($update_status["status"] == true) {
                                $status = true;
                                $ajax_reply->set_swap_tag_string("message", $lang["login.rn.info.1"]);
                                $ajax_reply->set_swap_tag_string("redirect", "login");
                            } else {
                                $failed_on = $lang["login.rn.error.6"];
                            }
                        } else {
                            $failed_on = $lang["login.rn.error.5"];
                        }
                    } else {
                        $failed_on = $lang["login.rn.error.4"];
                    }
                }
            }
        }
    } else {
        $failed_on =  $lang["login.rn.error.3"];
    }
} else {
    $failed_on = $lang["login.rn.error.2"];
}

if ($status == false) {
    if ($failed_on == "") {
        $ajax_reply->set_swap_tag_string("message", $lang["login.rn.error.1"]);
    } else {
        $ajax_reply->set_swap_tag_string("message", $failed_on);
    }
}
