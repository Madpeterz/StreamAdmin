<?php

$reply["hasmessage"] = 0;
$status = true;
if ($owner_override == true) {
    $message_set = new message_set();
    $message_set->loadNewest(1, [], [], "id", "ASC"); // lol loading oldest with newest command ^+^ hax
    if ($message_set->getCount() > 0) {
        $message = $message_set->getFirst();
        $avatar = new avatar();
        if ($avatar->loadID($message->getAvatarlink()) == true) {
            $remove_status = $messageremoveEntry();
            if ($remove_status["status"] == true) {
                $reply["hasmessage"] = 1;
                $reply["avataruuid"] = $avatar->getAvataruuid();
                echo $message->getMessage();
            } else {
                echo $lang["mailserver.n.error.4"];
            }
        } else {
            $remove_status = $messageremoveEntry();
            if ($remove_status["status"] == true) {
                echo $lang["mailserver.n.error.3"];
            } else {
                echo sprintf($lang["mailserver.n.error.2"], $message->getId());
            }
        }
    } else {
        echo "nowork";
    }
} else {
    echo $lang["mailserver.n.error.1"];
}
