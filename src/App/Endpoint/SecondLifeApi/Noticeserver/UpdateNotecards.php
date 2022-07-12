<?php

namespace App\Endpoint\SecondLifeApi\Noticeserver;

use App\Models\Noticenotecard;
use App\Models\Sets\NotecardmailSet;
use App\Models\Sets\NoticenotecardSet;
use App\Models\Sets\NoticeSet;
use App\Models\Sets\PackageSet;
use App\Template\SecondlifeAjax;
use YAPF\Framework\Responses\DbObjects\RemoveReply;
use YAPF\Framework\Responses\DbObjects\SetsLoadReply;

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
        if ($noticenotecardset->loadWithConfig($where_config)->status == false) {
            $this->failed("Unable to load notice notecard set [p1]");
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
        if ($noticenotecardset->loadFromIds($missing_ids)->status == false) {
            $this->failed("Unable to load notice notecard set [p2]");
            return false;
        }
        $status = $noticenotecardset->updateFieldInCollection("missing", 1);
        if ($status->status == false) {
            $this->failed($status->message);
            return false;
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
        $known_notecard_names = $noticenotecardset->uniqueNames();
        foreach ($active_notecards as $new_notecard) {
            if ($new_notecard != "none") {
                if (in_array($new_notecard, $known_notecard_names) == false) {
                    $noticeNotecard = new Noticenotecard();
                    $noticeNotecard->setName($new_notecard);
                    $noticeNotecard->setMissing(false);
                    $createStatus = $noticeNotecard->createEntry();
                    if ($createStatus->status == false) {
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
        if ($noticenotecardset->loadFromIds($found_ids) == false) {
            $this->setSwapTag("message", "Unable to load notice notecard set [p2]");
            return false;
        }
        $status = $noticenotecardset->updateFieldInCollection("missing", 0);
        if ($status->status == false) {
            $this->setSwapTag("message", $status->message);
        }
        return true;
    }

    protected function purgeMissingUnused(): RemoveReply
    {
        $noticenotecardset = new NotecardmailSet();
        $noticenotecardset->loadAll();

        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $used_notecard_ids = array_merge(
            $notice_set->uniqueNoticeNotecardLinks(),
            $noticenotecardset->uniqueNoticenotecardLinks()
        );

        $where_config = [
            "fields" => ["id","missing"],
            "values" => [1,1],
            "types" => ["i","i"],
            "matches" => ["!=","="],
        ];

        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadWithConfig($where_config) == false) {
            return new RemoveReply("Unable to load notice notecard set because: "
            . $noticenotecardset->getLastError());
        }

        $purge_ids = [];
        foreach ($noticenotecardset->getAllIds() as $notice_notecard_id) {
            if (in_array($notice_notecard_id, $used_notecard_ids) == false) {
                $purge_ids[] = $notice_notecard_id;
            }
        }

        if (count($purge_ids) == 0) {
            return new RemoveReply("Nothing todo", true);
        }

        if ($this->unlinkPackages(false, $purge_ids) == false) {
            return new RemoveReply("Unable to unlink notecards from package [Stage1]");
        }
        if ($this->unlinkPackages(true, $purge_ids) == false) {
            return new RemoveReply("Unable to unlink notecards from package [Stage2]");
        }

        $noticenotecardset = new NoticenotecardSet();
        if ($noticenotecardset->loadFromIds($purge_ids)->status == false) {
            return new RemoveReply("Failed to load");
        }
        if ($noticenotecardset->getCount() == 0) {
            return new RemoveReply("No notecards loaded in the set");
        }
        return $noticenotecardset->purgeCollection();
    }
    protected function unlinkPackages(bool $useOtherField, array $ids): bool
    {
        $packageSet_welcome = new PackageSet();
        $reply = new SetsLoadReply("not processed");
        $onfield = "welcomeNotecardLink";
        if ($useOtherField == false) {
            $reply = $packageSet_welcome->loadFromWelcomeNotecardLinks($ids);
        } elseif ($useOtherField == true) {
            $reply = $packageSet_welcome->loadFromSetupNotecardLinks($ids);
            $onfield = "setupNotecardLink";
        }
        if ($reply->status == false) {
            $this->addError("Load:" . $reply->message);
            return false;
        }
        if ($packageSet_welcome->getCount() > 0) {
            $reply = $packageSet_welcome->updateFieldInCollection($onfield, 1);
            if ($reply->status == false) {
                $this->addError("Update:" . $reply->message);
                return false;
            }
        }
        return true;
    }
    public function process(): void
    {
        $notecards = $this->input->post("notecards")->asString();
        $notecardsList = explode(",", $notecards);
        if ($this->markFound($notecardsList) == false) {
            return;
        }
        $purged = $this->purgeMissingUnused();
        if ($purged->status == false) {
            $this->setSwapTag("message", $purged->message);
            return;
        }
        if ($this->markMissing($notecardsList) == false) {
            return;
        }
        if ($this->addNew($notecardsList) == false) {
            return;
        }
        if ($purged->status == true) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "ok");
            if ($purged->itemsRemoved > 0) {
                $this->setSwapTag("message", "ok - purged: " . $purged->itemsRemoved . " notecards");
            }
        }
    }
}
