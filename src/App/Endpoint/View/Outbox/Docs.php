<?php

namespace App\Endpoint\View\Outbox;

use App\Models\Botconfig;
use App\Models\Set\NotecardmailSet;
use App\Models\Set\NoticenotecardSet;

class Docs extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Unsent static doc notecards");
        $table_head = ["id","Avatar name","Notecard"];
        $table_body = [];
        $notecardmail = new NotecardmailSet();
        $notecardmail->loadAll();
        $noticenotecards = $notecardmail->relatedNoticenotecard();
        $avatar_set = $notecardmail->relatedAvatar();
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
