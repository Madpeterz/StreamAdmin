<?php

namespace App\Endpoint\Secondlifeapi\Noticeserver;

use App\Models\Avatar;
use App\Models\Noticenotecard;
use App\Models\Set\NotecardmailSet;
use App\Template\SecondlifeAjax;

class Notecardmail extends SecondlifeAjax
{
    public function process(): void
    {
        $this->setSwapTag("recheck", false);
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
        if ($noticeNotecard->loadID($notecardmail->getNoticenotecardLink()) == false) {
            $this->setSwapTag("message", "Unable to load notecard from db");
            return;
        }

        if ($noticeNotecard->getMissing() == true) {
            // notecard is missing from DB clear this entry and continue
            $notecardmail->removeEntry();
            $this->setSwapTag("message", "Missing notecard please recheck");
            return ;
        }

        if ($noticeNotecard->getId() <= 1) {
            $notecardmail->removeEntry();
            $this->setSwapTag("message", "Invaild notecard please recheck");
            return ;
        }

        $avatar = new Avatar();
        if ($avatar->loadID($notecardmail->getAvatarLink()) == false) {
            $this->setSwapTag("message", "Unable to load avatar from db");
            return;
        }

        if ($notecardmail->removeEntry()->status == false) {
            $this->setSwapTag("message", "Unable to remove notecardMail from db");
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "send");
        $this->setSwapTag("avatarUUID", $avatar->getAvatarUUID());
        $this->setSwapTag("notecard", $noticeNotecard->getName());
    }
}
