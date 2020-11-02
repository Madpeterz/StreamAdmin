<?php

$input = new inputFilter();
$staffusername = $input->postFilter("staffusername");
$staffpassword = $input->postFilter("staffpassword");
$status = false;
if ((strlen($staffusername) > 0) && (strlen($staffpassword) > 0)) {
    if ($session->login_with_username_password($staffusername, $staffpassword) == true) {
        $status = true;
        $ajax_reply->set_swap_tag_string("message", $lang["login.st.info.1"]);
        $ajax_reply->set_swap_tag_string("redirect", "here");
    } else {
        $ajax_reply->set_swap_tag_string("message", $lang["login.st.error.1"]);
    }
} else {
    $ajax_reply->set_swap_tag_string("message", $lang["login.st.error.1"]);
}
