<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("rental_uid");
$rental = new rental();
$status = false;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    $stream = new stream();
    if ($stream->loadID($rental->getStreamlink()) == true) {
        $package = new package();
        if ($package->loadID($stream->getPackagelink()) == true) {
            $status = true;
            $reply["cost"] = $package->getCost();
            echo timeleft_hours_and_days($rental->getExpireunixtime());
        } else {
            echo $lang["renew.cat.error.3"];
        }
    } else {
        echo $lang["renew.cat.error.2"];
    }
} else {
    echo $lang["renew.cat.error.1"];
}
