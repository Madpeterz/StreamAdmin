<?php

$reply["hasmessage"] = 0;
$status = true;
if ($owner_override == true) {
    $message_set = new message_set();
    $message_set->load_newest(1, array(), array(), "id", "ASC"); // lol loading oldest with newest command ^+^ hax
    if ($message_set->get_count() > 0) {
        $message = $message_set->get_first();
        $avatar = new avatar();
        if ($avatar->load($message->get_avatarlink()) == true) {
            $remove_status = $message->remove_me();
            if ($remove_status["status"] == true) {
                $reply["hasmessage"] = 1;
                $reply["avataruuid"] = $avatar->get_avataruuid();
                echo $message->get_message();
            } else {
                echo $lang["mailserver.n.error.4"];
            }
        } else {
            $remove_status = $message->remove_me();
            if ($remove_status["status"] == true) {
                echo $lang["mailserver.n.error.3"];
            } else {
                echo sprintf($lang["mailserver.n.error.2"], $message->get_id());
            }
        }
    } else {
        echo "nowork";
    }
} else {
    echo $lang["mailserver.n.error.1"];
}
