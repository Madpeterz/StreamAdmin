<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Botconfig;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\NotecardmailSet;
use App\Models\Sets\NoticenotecardSet;

class Docs extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent static doc notecards");
        $table_head = ["id","Avatar name","Notecard"];
        $table_body = [];
        $noticenotecards = new NoticenotecardSet();
        $noticenotecards->loadAll();
        $notecardmail = new NotecardmailSet();
        $notecardmail->loadAll();
        $avatar_set = new AvatarSet();
        $avatar_set->loadByValues($notecardmail->getUniqueArray("avatarLink"));
        $botConfig = new Botconfig();
        $botConfig->loadID(1);
        foreach ($notecardmail as $staticnotecard) {
            $avatar = $avatar_set->getObjectByID($staticnotecard->getAvatarLink());
            $notecard = $noticenotecards->getObjectByID($staticnotecard->getNoticenotecardLink());
            $entry = [];
            $entry[] = $staticnotecard->getId();
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $entry[] = $notecard->getName();
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
