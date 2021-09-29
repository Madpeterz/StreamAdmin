<?php

namespace App\Endpoint\View\Outbox;

use App\R7\Model\Botconfig;
use App\R7\Set\AvatarSet;
use App\R7\Set\NotecardmailSet;
use App\R7\Set\NoticenotecardSet;

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
            $entry[] = '<a href="[[url_base]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $entry[] = $notecard->getName();
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
