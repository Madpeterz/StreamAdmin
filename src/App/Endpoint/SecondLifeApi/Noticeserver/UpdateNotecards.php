<?php

namespace App\Endpoint\SecondLifeApi\Noticeserver;

use App\R7\Model\Noticenotecard;
use App\R7\Set\NotecardmailSet;
use App\R7\Set\NoticenotecardSet;
use App\R7\Set\NoticeSet;
use App\Template\SecondlifeAjax;
use YAPF\InputFilter\InputFilter;

class UpdateNotecards extends SecondlifeAjax
{
    protected function markMissing(array $active_notecards = []): bool
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "types" => ["i"],
            "matches" => ["!="],
        ];
        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadWithConfig($where_config) == false) {
            $this->setSwapTag("message", "Unable to load notice notecard set [p1]");
            return false;
        }
        $missing_ids = [];
        foreach ($noticenotecardset->getAllIds() as $notice_notecard_id) {
            $noticeNotecard = $noticenotecardset->getObjectByID($notice_notecard_id);
            if (in_array($noticeNotecard->getName(), $active_notecards) == false) {
                $missing_ids[] = $notice_notecard_id;
            }
        }
        if (count($missing_ids) == 0) {
            return true;
        }
        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadIds($missing_ids) == false) {
            $this->setSwapTag("message", "Unable to load notice notecard set [p2]");
            return false;
        }
        $status = $noticenotecardset->updateFieldInCollection("missing", 1);
        if ($status["status"] == false) {
            $this->setSwapTag("message", $status["message"]);
        }
        return true;
    }
    protected function addNew(array $active_notecards = []): bool
    {
        $where_config = [
            "fields" => ["id"],
            "values" => [1],
            "types" => ["i"],
            "matches" => ["!="],
        ];
        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadWithConfig($where_config) == false) {
            $this->setSwapTag("message", "Unable to load notice notecard set [p1]");
            return false;
        }
        $known_notecard_names = $noticenotecardset->getUniqueArray("name");
        foreach ($active_notecards as $new_notecard) {
            if ($new_notecard != "none") {
                if (in_array($new_notecard, $known_notecard_names) == false) {
                    $noticeNotecard = new Noticenotecard();
                    $noticeNotecard->setName($new_notecard);
                    $noticeNotecard->setMissing(false);
                    if ($noticeNotecard->createEntry()["status"] == false) {
                        $this->setSwapTag("message", "Unable to create new notecard");
                        return false;
                    }
                }
            }
        }
        return true;
    }
    protected function markFound(array $active_notecards = []): bool
    {
        $where_config = [
            "fields" => ["id","missing"],
            "values" => [1,1],
            "types" => ["i","i"],
            "matches" => ["!=","="],
        ];
        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadWithConfig($where_config) == false) {
            $this->setSwapTag("message", "Unable to load notice notecard set [p1]");
            return false;
        }
        $found_ids = [];
        foreach ($noticenotecardset->getAllIds() as $notice_notecard_id) {
            $noticeNotecard = $noticenotecardset->getObjectByID($notice_notecard_id);
            if (in_array($noticeNotecard->getName(), $active_notecards) == true) {
                $found_ids[] = $notice_notecard_id;
            }
        }
        if (count($found_ids) == 0) {
            return true;
        }
        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadIds($found_ids) == false) {
            $this->setSwapTag("message", "Unable to load notice notecard set [p2]");
            return false;
        }
        $status = $noticenotecardset->updateFieldInCollection("missing", 0);
        if ($status["status"] == false) {
            $this->setSwapTag("message", $status["message"]);
        }
        return true;
    }
    /**
     * purgeMissingUnused
     * @return mixed[] [status =>  bool,removed_entrys => integer]
     */
    protected function purgeMissingUnused(): array
    {
        $noticenotecardset = new NotecardmailSet();
        $noticenotecardset->loadAll();

        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $used_notecard_ids = $notice_set->getUniqueArray("noticeNotecardLink");

        $where_config = [
            "fields" => ["id","missing","id"],
            "values" => [1,1,$noticenotecardset->getUniqueArray("noticenotecardLink")],
            "types" => ["i","i","i"],
            "matches" => ["!=","=","NOT IN"],
        ];
        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadWithConfig($where_config) == false) {
            return ["status" => false,"removed_entrys" => 0];
        }

        $purge_ids = [];
        foreach ($noticenotecardset->getAllIds() as $notice_notecard_id) {
            if (in_array($notice_notecard_id, $used_notecard_ids) == false) {
                $purge_ids[] = $notice_notecard_id;
            }
        }

        if (count($purge_ids) == 0) {
            return ["status" => true,"removed_entrys" => 0];
        }

        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadIds($purge_ids) == false) {
            return ["status" => false,"removed_entrys" => 0];
        }
        return $noticenotecardset->purgeCollection();
    }
    public function process(): void
    {
        $input = new InputFilter();
        $notecards = $input->postFilter("notecards");
        $notecardsList = explode(",", $notecards);
        if ($this->markFound($notecardsList) == false) {
            return;
        }
        $purged = $this->purgeMissingUnused();
        if ($this->markMissing($notecardsList) == false) {
            return;
        }
        if ($this->addNew($notecardsList) == false) {
            return;
        }
        if ($purged["status"] == true) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "ok");
            if ($purged["removed_entrys"] > 0) {
                $this->setSwapTag("message", "ok - purged: " . $purged["removed_entrys"] . " notecards");
            }
        }
    }
}
