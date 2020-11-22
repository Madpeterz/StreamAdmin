<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$status = false;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    if ($rental->getAvatarlink() == $object_owner_avatar->getId()) {
        $package = new package();
        if ($package->loadID($rental->getPackagelink()) == true) {
            $stream = new stream();
            if ($stream->loadID($rental->getStreamlink()) == true) {
                $server = new server();
                if ($server->loadID($stream->getServerlink()) == true) {
                    $servertypes = new servertypes();
                    if ($servertypes->loadID($package->getServertypelink()) == true) {
                        $status = true;
                        $reply["serverurl"] = "http://" . $server->getDomain() . ":" . $stream->getPort() . "";
                        $reply["servertype"] = $servertypes->getName();
                        echo "ok";
                    }
                }
            }
        }
    }
}
if ($status == false) {
    echo "noserver";
}
