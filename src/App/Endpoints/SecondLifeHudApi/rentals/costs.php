<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$status = false;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    if ($rental->getAvatarlink() == $object_owner_avatar->getId()) {
        $package = new package();
        if ($package->loadID($rental->get_packagelink()) == true) {
            $avatar_system = new avatar();
            if ($avatar_system->loadID($slconfig->get_owner_av()) == true) {
                $status = true;
                $reply["systemowner"] = $avatar_system->get_avataruuid();
                $reply["cost"] = $package->getCost();
                $reply["old_expire_time"] = $rental->get_expireunixtime();
            }
        }
    }
}
if ($status == false) {
    echo "unabletoload";
}
