<?php

$input = new inputFilter();
$targetuid = $input->postFilter("targetuid");
$avatar = new avatar();
$status = false;

if ($targetuid != null) {
    $bits = explode(" ", $targetuid);
    $load_status = false;
    if (count($bits) == 2) {
        $firstname = strtolower($bits[0]);
        $firstname = ucfirst($firstname);
        $lastname = strtolower($bits[1]);
        $lastname = ucfirst($lastname);
        $targetuid = "" . $firstname . " " . $lastname . "";
        $load_status = $avatar->loadByField("avatarname", $targetuid);
    } elseif (strlen($targetuid) == 36) {
        $load_status = $avatar->loadByField("avataruuid", $targetuid);
    }
    $status = true;
    $reply["dataset_count"] = 0;
    if ($load_status == true) {
        $rental_set = new rental_set();
        $rental_set->load_on_field("avatarlink", $avatar->getId());
        if ($rental_set->getCount() > 0) {
            $status = false;
            $stream_set = new stream_set();
            $stream_set->loadIds($rental_set->getAllByField("streamlink"));
            if ($stream_set->getCount() > 0) {
                $reply_dataset = [];
                foreach ($rental_set->getAllIds() as $rental_id) {
                    $rental = $rental_set->getObjectByID($rental_id);
                    $stream = $stream_set->getObjectByID($rental->getStreamlink());
                    if ($stream != null) {
                        $reply_dataset[] = "" . $rental->getRental_uid() . "|||" . $stream->getPort() . "";
                    }
                }
                if (count($reply_dataset) > 0) {
                    $status = true;
                    $reply["dataset_count"] = count($reply_dataset);
                    $reply["dataset"] = $reply_dataset;
                    echo sprintf($lang["proxyrenew.dt.info.1"], $avatar->getAvatarname());
                } else {
                    echo $lang["proxyrenew.dt.error.5"];
                }
            } else {
                echo $lang["proxyrenew.dt.error.4"];
            }
        } else {
            echo $lang["proxyrenew.dt.error.3"];
        }
    } else {
        echo $lang["proxyrenew.dt.error.2"];
    }
} else {
    echo $lang["proxyrenew.dt.error.1"];
}
