<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$status = false;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    if ($rental->getAvatarlink() == $object_owner_avatar->getId()) {
        $package = new package();
        if ($package->loadID($rental->getPackagelink()) == true) {
            $avatar_system = new avatar();
            if ($avatar_system->loadID($slconfig->getOwner_av()) == true) {
                $status = true;
                $reply["systemowner"] = $avatar_system->get_avataruuid();
                $reply["cost"] = $package->getCost();
                $reply["old_expire_time"] = $rental->getExpireunixtime();
            }
        }
    }
}
if ($status == false) {
    echo "unabletoload";
}
