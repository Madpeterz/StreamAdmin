<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$status = false;
if ($rental->load_by_field("rental_uid", $rental_uid) == true) {
    if ($rental->get_avatarlink() == $object_owner_avatar->get_id()) {
        $package = new package();
        if ($package->load($rental->get_packagelink()) == true) {
            $avatar_system = new avatar();
            if ($avatar_system->load($slconfig->get_owner_av()) == true) {
                $status = true;
                $reply["systemowner"] = $avatar_system->get_avataruuid();
                $reply["cost"] = $package->get_cost();
                $reply["old_expire_time"] = $rental->get_expireunixtime();
            }
        }
    }
}
if ($status == false) {
    echo "unabletoload";
}
