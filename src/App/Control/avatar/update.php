<?php

$input = new inputFilter();
$avatarname = $input->postFilter("avatarname");
$avataruuid = $input->postFilter("avataruuid");
$failed_on = "";
if (count(explode(" ", $avatarname)) == 1) {
    $avatarname .= " Resident";
}
if (strlen($avatarname) < 5) {
    $failed_on .= $lang["av.ud.error.1"];
} elseif (strlen($avatarname) > 125) {
    $failed_on .= $lang["av.ud.error.2"];
} elseif (strlen($avataruuid) != 36) {
    $failed_on .= $lang["av.ud.error.3"];
}
$ajax_reply->set_swap_tag_string("redirect", "avatar");
$status = false;
if ($failed_on == "") {
    $avatar = new avatar();
    if ($avatar->loadByField("avatar_uid", $this->page) == true) {
        $where_fields = [["avataruuid" => "="]];
        $where_values = [[$avataruuid => "s"]];
        $count_check = $sql->basic_count($avatar->get_table(), $where_fields, $where_values);
        $expected_count = 0;
        if ($avatar->get_avataruuid() == $avataruuid) {
            $expected_count = 1;
        }
        if ($count_check["status"] == true) {
            if ($count_check["count"] == $expected_count) {
                $avatar->setAvatarname($avatarname);
                $avatar->setAvataruuid($avataruuid);
                $update_status = $avatar->updateEntry();
                if ($update_status["status"] == true) {
                    $status = true;
                    $ajax_reply->set_swap_tag_string("message", $lang["av.ud.info.1"]);
                } else {
                    $ajax_reply->set_swap_tag_string("message", sprintf($lang["av.ud.error.7"], $update_status["message"]));
                }
            } else {
                $ajax_reply->set_swap_tag_string("message", $lang["av.ud.error.6"]);
            }
        } else {
            $ajax_reply->set_swap_tag_string("message", $lang["av.ud.error.5"]);
        }
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["av.ud.error.4"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $failed_on);
    $ajax_reply->set_swap_tag_string("redirect", null);
    $status = false;
}
