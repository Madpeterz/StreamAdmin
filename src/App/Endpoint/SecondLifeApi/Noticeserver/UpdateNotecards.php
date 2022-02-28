<?php

namespace App\Endpoint\SecondLifeApi\Noticeserver;

use App\Models\Noticenotecard;
use App\Models\Sets\NotecardmailSet;
use App\Models\Sets\NoticenotecardSet;
use App\Models\Sets\NoticeSet;
use App\Models\Sets\PackageSet;
use App\Template\SecondlifeAjax;

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
        if ($noticenotecardset->loadByValues($missing_ids) == false) {
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
                    $createStatus = $noticeNotecard->createEntry();
                    if ($createStatus["status"] == false) {
                        $this->setSwapTag("message", "Unable to create new notecard: " . $createStatus["message"]);
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
        if ($noticenotecardset->loadByValues($found_ids) == false) {
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
        $used_notecard_ids = array_merge(
            $notice_set->getUniqueArray("noticeNotecardLink"),
            $noticenotecardset->getUniqueArray("noticenotecardLink")
        );

        $where_config = [
            "fields" => ["id","missing"],
            "values" => [1,1],
            "types" => ["i","i"],
            "matches" => ["!=","="],
        ];

        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadWithConfig($where_config) == false) {
            return [
                "status" => false,
                "removed_entrys" => 0,
                "message" => "Unable to load notice notecard set because: "
                . $noticenotecardset->getLastError(),
            ];
        }

        $purge_ids = [];
        foreach ($noticenotecardset->getAllIds() as $notice_notecard_id) {
            if (in_array($notice_notecard_id, $used_notecard_ids) == false) {
                $purge_ids[] = $notice_notecard_id;
            }
        }

        if (count($purge_ids) == 0) {
            return [
                "status" => true,
                "removed_entrys" => 0,
                "message" => "Nothing todo",
            ];
        }

        if ($this->unlinkPackages("welcomeNotecardLink", $purge_ids) == false) {
            error_log($this->getLastError());
            return [
                "status" => false,
                "removed_entrys" => 0,
                "message" => "Unable to unlink notecards from package [Stage1]",
            ];
        }
        if ($this->unlinkPackages("setupNotecardLink", $purge_ids) == false) {
            error_log($this->getLastError());
            return [
                "status" => false,
                "removed_entrys" => 0,
                "message" => "Unable to unlink notecards from package [Stage2]",
            ];
        }

        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadByValues($purge_ids) == false) {
            return ["status" => false,"removed_entrys" => 0];
        }
        if ($noticenotecardset->getCount() == 0) {
            return [
                "status" => false,
                "removed_entrys" => 0,
                "message" => "No notecards loaded in the set",
            ];
        }
        $reply = $noticenotecardset->purgeCollection();
        if ($reply["status"] == false) {
            return [
                "status" => false,
                "removed_entrys" => 0,
                "message" => "Unable to remove unused notecards because: "
                . $reply["message"] . " ids: " . implode(",", $purge_ids),
            ];
        }

        return $reply;
    }
    protected function unlinkPackages(string $onfield, array $ids): bool
    {
        $packageSet_welcome = new PackageSet();
        $reply = $packageSet_welcome->loadByValues($ids, $onfield);
        if ($reply["status"] == false) {
            $this->addError(__FILE__, __FUNCTION__, "Load:" . $reply["message"]);
            return false;
        }
        if ($packageSet_welcome->getCount() > 0) {
            $reply = $packageSet_welcome->updateFieldInCollection($onfield, 1);
            if ($reply["status"] == false) {
                $this->addError(__FILE__, __FUNCTION__, "Update:" . $reply["message"]);
                return false;
            }
        }
        return true;
    }
    public function process(): void
    {

        $notecards = $this->post("notecards");
        $notecardsList = explode(",", $notecards);
        if ($this->markFound($notecardsList) == false) {
            return;
        }
        $purged = $this->purgeMissingUnused();
        if ($purged["status"] == false) {
            $this->setSwapTag("message", $purged["message"]);
            return;
        }
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
