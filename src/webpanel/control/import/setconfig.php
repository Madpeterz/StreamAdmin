<?php

if ($session->get_ownerlevel() == 1) {
    $input = new inputFilter();
    $db_host = $input->postFilter("db_host");
    $db_name = $input->postFilter("db_name");
    $db_username = $input->postFilter("db_username");
    $db_pass = $input->postFilter("db_pass");

    $saveconfig = '<?php $r4_db_host="' . $db_host . '"; $r4_db_name="' . $db_name . '"; $r4_db_username="' . $db_username . '"; $r4_db_pass="' . $db_pass . '";?>';
    if (file_exists("shared/config/r4.php") == true) {
        unlink("shared/config/r4.php");
    }
    file_put_contents("shared/config/r4.php", $saveconfig);
    $status = true;
    $ajax_reply->set_swap_tag_string("message", "ok");
    $ajax_reply->set_swap_tag_string("redirect", "import");
} else {
    $status = false;
    $ajax_reply->set_swap_tag_string("message", "Only the system owner can access this area");
    $ajax_reply->set_swap_tag_string("redirect", "");
}
