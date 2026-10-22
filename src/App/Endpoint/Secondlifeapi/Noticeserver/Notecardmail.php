<?php

namespace App\Endpoint\Secondlifeapi\Noticeserver;

use App\Models\Avatar;
use App\Models\Notecardmail as NotecardmailModel;
use App\Models\Noticenotecard;
use App\Models\Set\NotecardmailSet;
use App\Template\SecondlifeAjax;

class Notecardmail extends SecondlifeAjax
{
    /**
     * Drops a queue entry that can never be sent. The removal is committed straight away because
     * the request still ends with status false, which rolls back everything not yet committed.
     */
    protected function dropEntry(NotecardmailModel $notecardmail, string $message): void
    {
        $notecardmail->removeEntry();
        $this->siteConfig->getSQL()->sqlSave(false);
        $this->setSwapTag("message", $message);
    }

    public function process(): void
    {
        $this->setSwapTag("recheck", false);
        if ($this->hasAccessOwner() == false) {
            return;
        }
        $notecardmailSet = new NotecardmailSet();
        $notecardmailSet->loadNewest(limit:1, orderDirection:"ASC");
        if ($notecardmailSet->getCount() == 0) {
            $this->setSwapTag("status", true);
            $this->setSwapTag("message", "nowork");
            return;
        }
        $notecardmail = $notecardmailSet->getFirst();

        $noticeNotecard = new Noticenotecard();
        $this->setSwapTag("recheck", true);
        if ($noticeNotecard->loadID($notecardmail->_NoticenotecardLink)->status == false) {
            $this->dropEntry($notecardmail, "Unable to load notecard from db");
            return;
        }

        if ($noticeNotecard->_Missing == true) {
            // notecard is missing from the server inventory, clear this entry and continue
            $this->dropEntry($notecardmail, "Missing notecard please recheck");
            return;
        }

        if ($noticeNotecard->_Id <= 1) {
            $this->dropEntry($notecardmail, "Invaild notecard please recheck");
            return;
        }

        $avatar = new Avatar();
        if ($avatar->loadID($notecardmail->_AvatarLink)->status == false) {
            $this->dropEntry($notecardmail, "Unable to load avatar from db");
            return;
        }

        if ($notecardmail->removeEntry()->status == false) {
            $this->setSwapTag("message", "Unable to remove notecardMail from db");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "send");
        $this->setSwapTag("avatarUUID", $avatar->_AvatarUUID);
        $this->setSwapTag("notecard", $noticeNotecard->_Name);
    }
}
