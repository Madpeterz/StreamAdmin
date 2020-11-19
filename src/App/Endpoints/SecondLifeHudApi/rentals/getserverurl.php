<?php

$input = new inputFilter();
$rental_uid = $input->postFilter("uid");
$rental = new rental();
$status = false;
if ($rental->loadByField("rental_uid", $rental_uid) == true) {
    if ($rental->getAvatarlink() == $object_owner_avatar->getId()) {
        $package = new package();
        if ($package->load($rental->get_packagelink()) == true) {
            $stream = new stream();
            if ($stream->load($rental->get_streamlink()) == true) {
                $server = new server();
                if ($server->load($stream->get_serverlink()) == true) {
                    $servertypes = new servertypes();
                    if ($servertypes->load($package->getServertypelink()) == true) {
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
