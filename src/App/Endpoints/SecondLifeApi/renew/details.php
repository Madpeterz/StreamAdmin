<?php

$input = new inputFilter();
$avataruuid = $input->postFilter("avataruuid");
$avatar = new avatar();
$status = false;
$reply["dataset_count"] = 0;
if ($avatar->loadByField("avataruuid", $avataruuid) == true) {
    $banlist = new banlist();
    if ($banlist->loadByField("avatar_link", $avatar->getId()) == false) {
        $rental_set = new rental_set();
        $rental_set->loadOnField("avatarlink", $avatar->getId());
        if ($rental_set->getCount() > 0) {
            $stream_set = new stream_set();
            $stream_set->loadIds($rental_set->getAllByField("streamlink"));
            if ($stream_set->getCount() > 0) {
                $apirequests_set = new api_requests_set();
                $apirequests_set->loadAll();
                $used_stream_ids = $apirequests_set->getUniqueArray("streamlink");
                $reply_dataset = [];
                foreach ($rental_set->getAllIds() as $rental_id) {
                    $rental = $rental_set->getObjectByID($rental_id);
                    $stream = $stream_set->getObjectByID($rental->getStreamlink());
                    if ($stream != null) {
                        if (in_array($stream->getId(), $used_stream_ids) == false) {
                            $reply_dataset[] = "" . $rental->getRental_uid() . "|||" . $stream->getPort() . "";
                        }
                    }
                }
                if (count($reply_dataset) > 0) {
                    $status = true;
                    $reply["dataset_count"] = count($reply_dataset);
                    $reply["dataset"] = $reply_dataset;
                    echo sprintf($lang["renew.dt.info.2"], $avatar->getAvatarname());
                } else {
                    $status = true;
                    echo $lang["renew.dt.error.3"];
                }
            } else {
                echo $lang["renew.dt.error2"];
            }
        } else {
            $status = true;
            echo $lang["renew.dt.info.1"];
        }
    } else {
        echo $lang["renew.dt.error2.banned"];
    }
} else {
    $status = true;
    echo $lang["renew.dt.error.1"];
}
