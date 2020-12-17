<?php

$where_config = [
    "fields" => ["id"],
    "values" => [1],
    "types" => ["i"],
    "matches" => ["!="],
];
$status = true;
$notice_notecard_set = new notice_notecard_set();
$notice_notecard_set->loadWithConfig($where_config);
$notecards = $input->postFilter("notecards");
$notecards_list = [];
if ($notecards != "none") {
    if (strlen($notecards) > 0) {
        $notecards_list = explode(",", $notecards);
        // mark alive notecards / missing
        foreach ($notice_notecard_set->getAllIds() as $notice_notecard_id) {
            $notice_notecard = $notice_notecard_set->getObjectByID($notice_notecard_id);
            $notecards_list_index = array_search($notice_notecard->getName(), $notecards_list);
            if ($notecards_list_index !== false) {
                unset($notecards_list[$notecards_list_index]);
            }
            if ($notice_notecardgetMissing() == $notecards_list_index) {
                $notice_notecard->set_missing($notecards_list_index);
                $status = $notice_notecard->updateEntry()["status"];
                if ($status == false) {
                    echo $lang["noticeserver.up.error.1"];
                    break;
                }
            }
        }
        // new notecards
        if ($status == true) {
            foreach ($notecards_list as $notecardname) {
                $notice_notecard = new notice_notecard();
                $notice_notecard->setName($notecardname);
                $notice_notecard->set_missing(false);
                $status = $notice_notecard->createEntry();
                if ($status == true) {
                    $notice_notecard_set->addToCollected($notice_notecard);
                } else {
                    echo $lang["noticeserver.up.error.2"];
                    break;
                }
            }
        }
    } else {
        $status = false;
        echo $lang["noticeserver.up.error.3"];
    }
} else {
    if ($notice_notecard_set->getCount() > 0) {
        $status = $notice_notecard_set->update_single_field_for_collection("missing", 1)["status"];
        if ($status == false) {
            echo $lang["noticeserver.up.error.5"];
        }
    }
}
if ($status == true) {
    // remove dead notecards from db
    $notice_set = new notice_set();
    $notice_set->loadAll();
    if ($notice_set->getCount() > 0) {
        $used_notecards = $notice_set->getUniqueArray("notice_notecardlink");
        foreach ($notice_notecard_set->getAllIds() as $notice_notecard_id) {
            if (in_array($notice_notecard_id, $used_notecards) == false) {
                $notice_notecard = $notice_notecard_set->getObjectByID($notice_notecard_id);
                if ($notice_notecardgetMissing() == true) {
                    $status = $notice_notecardremoveEntry()["status"];
                    if ($status == false) {
                        echo $lang["noticeserver.up.error.4"];
                    }
                }
            }
        }
    }
}
if ($status == true) {
    echo "ok";
}
