<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$status = false;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    if ($rental->getAvatarlink() == $object_owner_avatar->getId()) {
        $package = new package();
        if ($package->loadID($rental->get_packagelink()) == true) {
            $stream = new stream();
            if ($stream->loadID($rental->get_streamlink()) == true) {
                $server = new server();
                if ($server->loadID($stream->get_serverlink()) == true) {
                    $servertypes = new servertypes();
                    if ($servertypes->loadID($package->getServertypelink()) == true) {
                        $status = true;
                        $reply["serverurl"] = "http://" . $server->get_domain() . ":" . $stream->get_port() . "";
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
