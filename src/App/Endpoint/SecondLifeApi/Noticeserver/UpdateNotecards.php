<?php

namespace App\Endpoint\SecondLifeApi\Noticeserver;

use App\R7\Model\Noticenotecard;
use App\R7\Set\NoticenotecardSet;
use App\R7\Set\NoticeSet;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class UpdateNotecards extends SecondlifeAjax
{
    public function process(): void
    {
        $where_config = [
        "fields" => ["id"],
        "values" => [1],
        "types" => ["i"],
        "matches" => ["!="],
        ];
        $status = true;
        $input = new InputFilter();
        $notice_notecard_set = new NoticenotecardSet();
        $notice_notecard_set->loadWithConfig($where_config);
        $notecards = $input->postFilter("notecards");
        $notecards_list = [];
        if ($notecards == "none") {
            if ($notice_notecard_set->getCount() > 0) {
                $status = $notice_notecard_set->updateFieldInCollection("missing", 1);
                if ($status["status"] == false) {
                    $this->setSwapTag("message", "Unable to mark static notecards as missing");
                    return;
                }
            }
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "ok");
            return;
        }

        if (strlen($notecards) == 0) {
            $this->setSwapTag("message", "Expected notecards list not sent!");
            return;
        }

        $notecards_list = explode(",", $notecards);

        $missing_ids = [];
        $found_ids = [];
        // mark alive notecards / missing
        foreach ($notice_notecard_set->getAllIds() as $notice_notecard_id) {
            if ($notice_notecard_id != 1) {
                $notice_notecard = $notice_notecard_set->getObjectByID($notice_notecard_id);
                if (in_array($notice_notecard->getName(), $notecards_list) == false) {
                    if ($notice_notecard->getMissing() == 0) {
                        $missing_ids[] = $notice_notecard_id;
                    }
                } else {
                    if ($notice_notecard->getMissing() == 0) {
                        $found_ids[] = $notice_notecard_id;
                    }
                }
            }
        }

        $all_updates_ok = true;
        if (count($missing_ids) > 0) {
            $all_updates_ok = false;
            $notice_notecard_set_missing = new NoticenotecardSet();
            if ($notice_notecard_set_missing->loadIds($missing_ids)["status"] == true) {
                $all_updates_ok = $notice_notecard_set_missing->updateFieldInCollection("missing", true)["status"];
            }
        }
        if ($all_updates_ok == true) {
            if (count($found_ids) > 0) {
                $all_updates_ok = false;
                $notice_notecard_set_found = new NoticenotecardSet();
                if ($notice_notecard_set_found->loadIds($missing_ids)["status"] == true) {
                    $all_updates_ok = $notice_notecard_set_found->updateFieldInCollection("missing", false)["status"];
                }
            }
        }

        if ($all_updates_ok == false) {
            $this->setSwapTag("status", false);
            $this->setSwapTag("message", "unable to update missing/found status");
        }

        // new notecards
        if ($status == true) {
            foreach ($notecards_list as $notecardname) {
                $notice_notecard = new Noticenotecard();
                $notice_notecard->setName($notecardname);
                $notice_notecard->setMissing(false);
                $status = $notice_notecard->createEntry();
                if ($status == false) {
                    $this->setSwapTag("message", "Unable to create static notecard entry");
                    return;
                }
                $notice_notecard_set->addToCollected($notice_notecard);
            }
        }
        // remove dead notecards from db
        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $used_notecard_ids = $notice_set->getUniqueArray("noticeNotecardLink");
        $notice_notecard_set = new NoticenotecardSet();
        $notice_notecard_set->loadByField("missing", 1);
        $purgeids = [];
        foreach ($notice_notecard_set->getAllIds() as $notice_notecard_id) {
            if ($notice_notecard_id != 1) {
                if (in_array($notice_notecard_id, $used_notecard_ids) == false) {
                    $purgeids[] = $notice_notecard_id;
                }
            }
        }

        if (count($purgeids) > 0) {
            $notice_notecard_set = new NoticenotecardSet();
            $notice_notecard_set->loadIds($purgeids);
            if ($notice_notecard_set->purgeCollection()["status"] == false) {
                $this->setSwapTag("status", false);
                $this->setSwapTag("message", "unable to remove old notecards");
            }
        }

        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "ok");
    }
}
