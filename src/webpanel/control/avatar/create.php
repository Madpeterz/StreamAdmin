<?php

$avatar = new avatar();
$input = new inputFilter();
$avatarname = $input->postFilter("avatarname");
$avataruuid = $input->postFilter("avataruuid");
$failed_on = "";
if (count(explode(" ", $avatarname)) == 1) {
    $avatarname .= " Resident";
}
if (strlen($avatarname) < 5) {
    $failed_on .= $lang["av.cr.error.1"];
} elseif (strlen($avatarname) > 125) {
    $failed_on .= $lang["av.cr.error.2"];
} elseif (strlen($avataruuid) != 36) {
    $failed_on .= $lang["av.cr.error.3"];
} elseif ($avatar->load_by_field("avataruuid", $avataruuid) == true) {
    $failed_on .= $lang["av.cr.error.4"];
}
$status = false;
if ($failed_on == "") {
    $avatar_helper = new avatar_helper();
    $status = $avatar_helper->load_or_create($avataruuid, $avatarname);
    if ($status == true) {
        $status = true;
        $ajax_reply->set_swap_tag_string("message", $lang["av.cr.info.1"]);
        $ajax_reply->set_swap_tag_string("redirect", "avatar");
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["av.cr.error.5"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $failed_on);
}
