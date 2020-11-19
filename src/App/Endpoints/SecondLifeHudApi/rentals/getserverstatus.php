<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$status = false;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    if ($rental->getAvatarlink() == $object_owner_avatar->getId()) {
        if ($rental->get_expireunixtime() > time()) {
            $reply["timeleft"] = "Timeleft: " . timeleft_hours_and_days($rental->get_expireunixtime());
            $reply["expires"] = "Renewal due by: " . date('l jS \of F Y h:i:s A', $rental->get_expireunixtime());
        } else {
            $reply["timeleft"] = "Expired: " . expired_ago($rental->get_expireunixtime());
            $reply["expires"] = "Expired: " . date('l jS \of F Y h:i:s A', $rental->get_expireunixtime());
        }
        echo "ok";
        $status = true;
    }
}
if ($status == false) {
    echo "noserver";
}
