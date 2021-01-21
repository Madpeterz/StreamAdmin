<?php

namespace App\Endpoint\SecondLifeApi\Noticeserver;

use App\Models\Noticenotecard;
use App\Models\NoticenotecardSet;
use App\Models\NoticeSet;
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
            $this->setSwapTag("status", "true");
            $this->setSwapTag("message", "Old notecards removed");
            return;
        }

        if (strlen($notecards) == 0) {
            $this->setSwapTag("message", "Expected notecards list not sent!");
            return;
        }

        $notecards_list = explode(",", $notecards);
        // mark alive notecards / missing
        foreach ($notice_notecard_set->getAllIds() as $notice_notecard_id) {
            $notice_notecard = $notice_notecard_set->getObjectByID($notice_notecard_id);
            $notecards_list_index = array_search($notice_notecard->getName(), $notecards_list);
            if ($notecards_list_index !== false) {
                unset($notecards_list[$notecards_list_index]);
            }
            if ($notice_notecard->getMissing() == $notecards_list_index) {
                $notice_notecard->setMissing($notecards_list_index);
                $status = $notice_notecard->updateEntry()["status"];
                if ($status == false) {
                    $this->setSwapTag(
                        "message",
                        "Unable to mark a single static notecard as missing"
                    );
                    return;
                }
            }
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
        if ($notice_set->getCount() > 0) {
            $used_notecards = $notice_set->getUniqueArray("noticeNotecardLink");
            foreach ($notice_notecard_set->getAllIds() as $notice_notecard_id) {
                if (in_array($notice_notecard_id, $used_notecards) == false) {
                    $notice_notecard = $notice_notecard_set->getObjectByID($notice_notecard_id);
                    if ($notice_notecard->getMissing() == true) {
                        $status = $notice_notecard->removeEntry()["status"];
                        if ($status == false) {
                            $this->setSwapTag("message", "Unable to remove static notecard entry");
                            return;
                        }
                    }
                }
            }
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "ok");
    }
}
